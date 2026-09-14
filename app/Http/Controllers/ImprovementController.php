<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Improvement;
use App\Models\PlagiarismCheck;
use App\Services\HistoryService;
use App\Services\ImprovementService;
use App\Services\PlagiarismService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use ZipArchive;

class ImprovementController extends Controller
{
    public function __construct(
        private ImprovementService $improvementService,
        private PlagiarismService $plagiarismService,
        private HistoryService $historyService,
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $improvements = Improvement::where('user_id', $user->id)
            ->with(['document', 'plagiarismCheck'])
            ->latest()
            ->paginate(10);

        $completedChecks = PlagiarismCheck::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with('document')
            ->latest()
            ->take(20)
            ->get();

        $layout = str_starts_with(request()->route()?->getName() ?? '', 'admin.') ? 'layouts.admin' : 'layouts.user';

        return view('improvement.index', array_merge(compact('improvements', 'completedChecks'), ['layout' => $layout]));
    }

    public function analyze(Request $request): RedirectResponse
    {
        $request->validate([
            'file'               => 'nullable|file|mimes:pdf,docx,txt',
            'plagiarism_check_id' => 'nullable|exists:plagiarism_checks,id',
            'mode'               => 'required|in:automatic,manual',
        ]);

        $user = auth()->user();
        $content = '';
        $document = null;
        $plagiarismCheck = null;

        // If coming from a plagiarism check
        if ($request->plagiarism_check_id) {
            $plagiarismCheck = PlagiarismCheck::findOrFail($request->plagiarism_check_id);
            abort_if($plagiarismCheck->user_id !== $user->id, 403);
            $document = $plagiarismCheck->document;
            $content = $document->content ?? '';
        } elseif ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('documents', 'public');
            $fullPath = Storage::disk('public')->path($path);
            $content = $this->plagiarismService->extractTextFromFile($fullPath, $file->getMimeType());

            $document = Document::create([
                'user_id'           => $user->id,
                'title'             => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'file_path'         => $path,
                'original_filename' => $file->getClientOriginalName(),
                'type'              => 'improvement',
                'status'            => 'processing',
                'content'           => $content,
                'file_size'         => $file->getSize(),
                'mime_type'         => $file->getMimeType(),
            ]);
        }

        if (empty(trim($content))) {
            return back()->withErrors(['error' => 'Tidak dapat mengekstrak konten dari file.']);
        }

        $improvement = Improvement::create([
            'user_id'             => $user->id,
            'document_id'         => $document?->id,
            'plagiarism_check_id' => $plagiarismCheck?->id,
            'original_content'    => $content,
            'mode'                => $request->mode,
            'status'              => 'analyzing',
            'original_similarity' => $plagiarismCheck?->total_similarity ?? null,
        ]);

        // Analyze
        $improvement = $this->improvementService->analyze($improvement, $plagiarismCheck);

        // If automatic mode, apply all immediately
        if ($request->mode === 'automatic') {
            $improvement = $this->improvementService->applyAll($improvement);
        }

        $document?->update(['status' => 'completed']);

        $routePrefix = str_starts_with(request()->route()?->getName() ?? '', 'admin.') ? 'admin' : 'user';

        return redirect()->route($routePrefix . '.improvement.show', $improvement->id)
            ->with('success', 'Analisis selesai! Silakan tinjau saran perbaikan.');
    }

    public function show(Improvement $improvement): View
    {
        abort_if($improvement->user_id !== auth()->id(), 403);

        $layout = str_starts_with(request()->route()?->getName() ?? '', 'admin.') ? 'layouts.admin' : 'layouts.user';

        return view('improvement.show', array_merge(compact('improvement'), ['layout' => $layout]));
    }

    public function apply(Request $request, Improvement $improvement): RedirectResponse
    {
        abort_if($improvement->user_id !== auth()->id(), 403);

        $request->validate([
            'mode'     => 'required|in:all,selected',
            'selected' => 'nullable|array',
            'selected.*' => 'integer',
        ]);

        if ($request->mode === 'all') {
            $improvement = $this->improvementService->applyAll($improvement);
        } else {
            $improvement = $this->improvementService->applySelected($improvement, $request->input('selected', []));
        }

        $document = $improvement->document;
        $sourcePath = $document && $document->file_path
            ? Storage::disk('public')->path($document->file_path)
            : null;

        // Original DOCX is kept as source of truth; only the improved text is stored for generated downloads.

        // Log history
        $docTitle = $improvement->document?->title ?? 'Dokumen';
        $this->historyService->logImprovement(
            auth()->user(),
            $improvement->id,
            $docTitle,
            (float) $improvement->original_similarity,
            (float) $improvement->improved_similarity
        );

        $routePrefix = str_starts_with(request()->route()?->getName() ?? '', 'admin.') ? 'admin' : 'user';

        return redirect()->route($routePrefix . '.improvement.show', $improvement->id)
            ->with('success', 'Perbaikan berhasil diterapkan!');
    }

    public function download(Improvement $improvement)
    {
        abort_if($improvement->user_id !== auth()->id(), 403);

        $content = trim((string) ($improvement->improved_content ?? $improvement->original_content ?? ''));

        $document = $improvement->document;
        $sourcePath = $document && $document->file_path
            ? Storage::disk('public')->path($document->file_path)
            : null;

        if ($sourcePath && is_file($sourcePath) && str_ends_with(strtolower($document->original_filename ?? ''), '.docx')) {
            $patchedPath = $this->buildPatchedDocxCopy($sourcePath, $improvement);

            return response()->download($patchedPath, $document->original_filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])->deleteFileAfterSend(true);
        }

        $originalName = $document?->original_filename ?? 'improved_document';
        $baseName = pathinfo($originalName, PATHINFO_FILENAME) ?: 'improved_document';
        $filename = $baseName . '_revised.docx';

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->getDocInfo()->setTitle($baseName);
        $phpWord->getDocInfo()->setCreator(auth()->user()?->name ?? 'NaskahCek');

        $section = $phpWord->addSection([
            'marginTop' => 720,
            'marginBottom' => 720,
            'marginLeft' => 720,
            'marginRight' => 720,
        ]);

        $paragraphs = preg_split("/\r\n|\r|\n{2,}/", $content) ?: [];
        foreach ($paragraphs as $paragraph) {
            $cleanParagraph = trim((string) $paragraph);
            if ($cleanParagraph === '') {
                $section->addTextBreak(1);
                continue;
            }

            $section->addText($cleanParagraph, ['name' => 'Times New Roman', 'size' => 11], [
                'spaceAfter' => 120,
                'alignment' => 'both',
            ]);
        }

        if ($content === '') {
            $section->addText('Dokumen hasil perbaikan kosong.', ['name' => 'Times New Roman', 'size' => 11]);
        }

        $tempBasePath = tempnam(sys_get_temp_dir(), 'improvement_');
        if ($tempBasePath === false) {
            throw new \RuntimeException('Unable to create a temporary file for the improved document export.');
        }

        $tempPath = $tempBasePath . '.docx';
        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempPath);

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    private function buildPatchedDocxCopy(string $sourceDocxPath, Improvement $improvement): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'patched_improvement_');
        if ($tempPath === false) {
            throw new \RuntimeException('Tidak dapat membuat file temporer untuk hasil DOCX perbaikan.');
        }

        $patchedPath = $tempPath . '.docx';
        if (!copy($sourceDocxPath, $patchedPath)) {
            @unlink($tempPath);
            throw new \RuntimeException('Tidak dapat menyalin DOCX asli ke versi hasil perbaikan.');
        }

        $zip = new ZipArchive();
        $opened = $zip->open($patchedPath);
        if ($opened !== true) {
            @unlink($patchedPath);
            throw new \RuntimeException('Gagal membuka salinan DOCX untuk diterapkan perbaikan.');
        }

        $documentXml = $zip->getFromName('word/document.xml');
        if ($documentXml === false) {
            $zip->close();
            @unlink($patchedPath);
            throw new \RuntimeException('Struktur DOCX tidak valid: word/document.xml tidak ditemukan.');
        }

        $patchedDocumentXml = $this->patchDocumentXml($documentXml, $this->buildReplacementPairs($improvement));
        $zip->addFromString('word/document.xml', $patchedDocumentXml);
        $zip->close();

        return $patchedPath;
    }

    private function buildReplacementPairs(Improvement $improvement): array
    {
        $pairs = [];

        foreach (($improvement->suggestions ?? []) as $suggestion) {
            $original = trim((string) ($suggestion['original'] ?? ''));
            $revised = trim((string) ($suggestion['suggestion'] ?? ''));

            if ($original !== '' && $revised !== '' && $original !== $revised) {
                $pairs[] = [$original, $revised];
            }
        }

        if ($pairs !== []) {
            return $pairs;
        }

        $originalContent = trim((string) ($improvement->original_content ?? ''));
        $improvedContent = trim((string) ($improvement->improved_content ?? ''));

        if ($originalContent === '' || $improvedContent === '') {
            return [];
        }

        $originalParagraphs = preg_split('/\r\n|\r|\n{2,}/', $originalContent) ?: [];
        $improvedParagraphs = preg_split('/\r\n|\r|\n{2,}/', $improvedContent) ?: [];

        foreach ($originalParagraphs as $index => $originalParagraph) {
            $normalizedOriginal = trim((string) $originalParagraph);
            if ($normalizedOriginal === '') {
                continue;
            }

            $normalizedImproved = trim((string) ($improvedParagraphs[$index] ?? ''));
            if ($normalizedImproved !== '' && $normalizedImproved !== $normalizedOriginal) {
                $pairs[] = [$normalizedOriginal, $normalizedImproved];
            }
        }

        return $pairs;
    }

    private function patchDocumentXml(string $documentXml, array $replacements): string
    {
        if ($replacements === []) {
            return $documentXml;
        }

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($documentXml);

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $paragraphNodes = $xpath->query('//w:p');
        if ($paragraphNodes === false || $paragraphNodes->length === 0) {
            return $documentXml;
        }

        foreach ($paragraphNodes as $paragraphNode) {
            foreach ($replacements as [$originalText, $revisedText]) {
                $this->replaceTextInParagraph($paragraphNode, $originalText, $revisedText, $xpath, $dom);
            }
        }

        return $dom->saveXML() ?: $documentXml;
    }

    private function replaceTextInParagraph(\DOMNode $paragraphNode, string $originalText, string $revisedText, DOMXPath $xpath, DOMDocument $dom): void
    {
        $originalText = trim($originalText);
        if ($originalText === '') {
            return;
        }

        $runNodes = $xpath->query('./w:r', $paragraphNode);
        if ($runNodes === false || $runNodes->length === 0) {
            return;
        }

        $runTexts = [];
        $fullText = '';
        $cursor = 0;

        foreach ($runNodes as $runNode) {
            $textNodes = $xpath->query('./w:t', $runNode);
            $value = '';
            foreach ($textNodes as $textNode) {
                $value .= $textNode->textContent;
            }

            $runTexts[] = ['run' => $runNode, 'text' => $value, 'start' => $cursor];
            $cursor += strlen($value);
            $fullText .= $value;
        }

        $matchStart = strpos($fullText, $originalText);
        if ($matchStart === false) {
            return;
        }

        $matchEnd = $matchStart + strlen($originalText);
        $firstIndex = null;
        $lastIndex = null;

        foreach ($runTexts as $index => $runText) {
            $runStart = $runText['start'];
            $runEnd = $runStart + strlen($runText['text']);
            if ($runEnd > $matchStart && $runStart < $matchEnd) {
                $firstIndex = $firstIndex ?? $index;
                $lastIndex = $index;
            }
        }

        if ($firstIndex === null || $lastIndex === null) {
            return;
        }

        $firstRun = $runTexts[$firstIndex]['run'];
        $firstRunText = $runTexts[$firstIndex]['text'];
        $firstRunStart = $runTexts[$firstIndex]['start'];
        $firstRunPrefixLength = max(0, $matchStart - $firstRunStart);
        $firstRunPrefix = substr($firstRunText, 0, $firstRunPrefixLength);

        if ($firstIndex === $lastIndex) {
            $matchOffsetInFirst = max(0, $matchStart - $firstRunStart);
            $matchLengthInFirst = strlen($originalText);
            $suffixAfterMatch = substr($firstRunText, $matchOffsetInFirst + $matchLengthInFirst);
            $this->setRunText($firstRun, $firstRunPrefix . $revisedText . $suffixAfterMatch, $xpath, $dom);
            return;
        }

        $firstRunEnd = $firstRunStart + strlen($firstRunText);
        $firstRunSuffix = ($matchEnd <= $firstRunEnd) ? substr($firstRunText, $matchStart - $firstRunStart + strlen($originalText)) : '';
        $this->setRunText($firstRun, $firstRunPrefix . $revisedText . $firstRunSuffix, $xpath, $dom);

        $lastRun = $runTexts[$lastIndex]['run'];
        $lastRunText = $runTexts[$lastIndex]['text'];
        $lastRunStart = $runTexts[$lastIndex]['start'];
        $suffixStart = max(0, $matchEnd - $lastRunStart);
        $lastRunSuffix = substr($lastRunText, $suffixStart);
        $this->setRunText($lastRun, $lastRunSuffix, $xpath, $dom);

        for ($i = $firstIndex + 1; $i < $lastIndex; $i++) {
            $paragraphNode->removeChild($runTexts[$i]['run']);
        }
    }

    private function setRunText(DOMElement $runNode, string $text, DOMXPath $xpath, DOMDocument $dom): void
    {
        $textNodes = $xpath->query('./w:t', $runNode);
        if ($textNodes !== false) {
            foreach ($textNodes as $textNode) {
                $runNode->removeChild($textNode);
            }
        }

        if ($text === '') {
            $runNode->parentNode?->removeChild($runNode);
            return;
        }

        $newTextNode = $dom->createElementNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'w:t', $text);
        $newTextNode->setAttribute('xml:space', 'preserve');
        $runNode->appendChild($newTextNode);
    }
}
