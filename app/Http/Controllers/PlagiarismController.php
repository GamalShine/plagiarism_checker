<?php

namespace App\Http\Controllers;

use App\Jobs\GeneratePlagiarismExport;
use App\Jobs\ProcessPlagiarismCheck;
use App\Models\Document;
use App\Models\PlagiarismCheck;
use App\Services\HistoryService;
use App\Services\PlagiarismExportService;
use App\Services\PlagiarismService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PlagiarismController extends Controller
{
    public function __construct(
        private PlagiarismService $plagiarismService,
        private HistoryService $historyService,
        private PlagiarismExportService $plagiarismExportService,
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $settings = $user->settings;
        $recentChecks = PlagiarismCheck::where('user_id', $user->id)
            ->with('document')
            ->latest()
            ->take(10)
            ->get();

        return view('plagiarism.index', array_merge(
            compact('settings', 'recentChecks'),
            $this->viewContext()
        ));
    }

    public function extractChapters(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,docx,txt',
        ]);

        $file = $request->file('file');
        $path = $file->store('temp_documents', 'public');
        $fullPath = Storage::disk('public')->path($path);

        $text = $this->plagiarismService->extractTextFromFile($fullPath, $file->getMimeType());

        $pattern = '/\b(?:BAB\s+[IVXLCDM0-9]+|ABSTRAK|KATA PENGANTAR|DAFTAR ISI|DAFTAR PUSTAKA|LAMPIRAN)\b/ui';
        preg_match_all($pattern, $text, $matches);

        @unlink($fullPath);

        $rawChapters = array_map('strtoupper', array_map('trim', $matches[0] ?? []));
        $chapters = [];

        foreach ($rawChapters as $c) {
            $key = null;
            if (str_starts_with($c, 'BAB')) {
                preg_match('/BAB\s+([IVXLCDM0-9]+)/i', $c, $m);
                if (!empty($m[1])) {
                    $key = (string) $this->romanToArabic($m[1]);
                }
            } else {
                $key = strtolower(str_replace(' ', '_', $c));
            }
            if ($key && !in_array($key, $chapters)) {
                $chapters[] = $key;
            }
        }

        return response()->json(['chapters' => $chapters]);
    }

    private function romanToArabic(string $roman): int
    {
        $roman = strtoupper($roman);
        if (is_numeric($roman)) return (int) $roman;

        $romans = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1,
        ];

        $result = 0;
        foreach ($romans as $k => $v) {
            while (strpos($roman, $k) === 0) {
                $result += $v;
                $roman = substr($roman, strlen($k));
            }
        }

        return $result;
    }

    public function check(Request $request): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user?->isAdmin() || ($user?->role === 'user' && $user->hasActivePackage()), 403, 'Paket aktif diperlukan untuk melakukan pengecekan.');

        $request->validate([
            'file'    => 'required|file|mimes:pdf,docx,txt',
            'sources' => 'required|array|min:1',
            'sources.*' => 'in:web,google_scholar,elsevier,openalex,crossref,crossref_posted,publications',
        ]);

        $file = $request->file('file');
        $chapters = [];

        if (! $user->isAdmin()) {
            $user->decrement('package_credits');
        }

        // Store file
        $path = $file->store('documents', 'public');
        $originalName = $file->getClientOriginalName();

        $chapters = $this->plagiarismService->normalizeSelectedChapterKeys($request->input('chapters', []));

        // Create document
        $document = Document::create([
            'user_id'           => $user->id,
            'title'             => pathinfo($originalName, PATHINFO_FILENAME),
            'file_path'         => $path,
            'original_filename' => $originalName,
            'type'              => 'plagiarism',
            'status'            => 'processing',
            'content'           => null,
            'file_size'         => $file->getSize(),
            'mime_type'         => $file->getMimeType(),
        ]);

        $check = PlagiarismCheck::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'status' => 'processing',
            'sources_checked' => $request->input('sources'),
            'chapters' => $chapters,
        ]);

        ProcessPlagiarismCheck::dispatch($check->id, $chapters);

        return redirect()->route($this->routePrefix() . '.plagiarism.result', $check->id)
            ->with('success', 'Pengecekan plagiarisme telah dimulai. Hasil akan muncul setelah proses selesai.');
    }

    public function result(PlagiarismCheck $plagiarismCheck): View
    {
        Gate::authorize('view', $plagiarismCheck);

        $resultHighlightLimit = max(50, (int) env('RESULT_HIGHLIGHT_LIMIT', 500));
        $check = $plagiarismCheck->load([
            'document',
            'sources' => function ($query) {
                $query->orderBy('similarity_score', 'desc');
            },
            'highlights' => function ($query) use ($resultHighlightLimit) {
                $query->select([
                    'id',
                    'plagiarism_check_id',
                    'plagiarism_source_id',
                    'original_text',
                    'matched_text',
                    'color_code',
                    'start_position',
                    'end_position',
                    'match_percentage',
                ])
                    ->orderBy('start_position')
                    ->limit($resultHighlightLimit);
            },
            'highlights.source',
        ]);

        $sourceIndexMap = [];
        $index = 1;
        foreach ($check->sources as $source) {
            $source->turnitin_index = $index;
            $sourceIndexMap[$source->id] = $index;
            $index++;
        }

        $highlightCacheKey = 'plagiarism:result-html:' . $check->id . ':' . ($check->updated_at?->timestamp ?? 0);
        $highlightedText = function_exists('shell_exec')
            ? Cache::rememberForever($highlightCacheKey, fn () => $this->buildHighlightedText($check, $sourceIndexMap))
            : '';

        return view('plagiarism.result', array_merge(
            compact('check', 'highlightedText', 'sourceIndexMap'),
            $this->viewContext()
        ));
    }

    public function status(PlagiarismCheck $plagiarismCheck)
    {
        Gate::authorize('view', $plagiarismCheck);

        return response()->json([
            'status' => $plagiarismCheck->status,
            'error_message' => $plagiarismCheck->error_message,
        ]);
    }

    public function updateSimilarity(Request $request, PlagiarismCheck $plagiarismCheck)
    {
        Gate::authorize('update', $plagiarismCheck);

        $validated = $request->validate([
            'total_similarity' => 'required|numeric|min:0|max:100',
        ]);

        $oldSimilarity = $plagiarismCheck->total_similarity;
        $plagiarismCheck->update([
            'total_similarity' => $validated['total_similarity'],
        ]);

        $this->historyService->log(
            auth()->user(),
            'update_similarity',
            "Mengubah overall similarity hasil cek plagiarisme: {$oldSimilarity}% → {$validated['total_similarity']}% untuk dokumen \"{$plagiarismCheck->document->title}\""
        );

        return response()->json([
            'message' => 'Overall similarity berhasil diperbarui',
            'total_similarity' => $plagiarismCheck->total_similarity,
            'similarity_color' => $plagiarismCheck->similarity_color,
            'similarity_label' => $plagiarismCheck->similarity_label,
        ]);
    }

    public function export(Request $request, PlagiarismCheck $plagiarismCheck)
    {
        Gate::authorize('view', $plagiarismCheck);

        $check = $plagiarismCheck->load(['document', 'user', 'sources' => function ($query) {
            $query->orderBy('similarity_score', 'desc');
        }, 'highlights.source']);

        $sourceIndexMap = [];
        $index = 1;
        foreach ($check->sources as $source) {
            $source->turnitin_index = $index;
            $sourceIndexMap[$source->id] = $index;
            $index++;
        }

        $downloadName = 'plagiarism_report_' . $check->id . '.pdf';
        $exportPath = $this->plagiarismExportService->getExportDiskPath($check);

        if (Storage::disk('public')->exists($exportPath)) {
            return response()->download(Storage::disk('public')->path($exportPath), $downloadName, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        if ($this->plagiarismExportService->shouldQueueExport()) {
            GeneratePlagiarismExport::dispatch($check->id, $downloadName, false);

            $this->historyService->log(
                auth()->user(),
                'export',
                "Export hasil cek plagiarisme: \"{$check->document->title}\" (antrian background)"
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'queued',
                    'message' => 'Export PDF sedang diproses di background. Silakan tunggu beberapa saat lalu refresh halaman.',
                ], 202);
            }

            return redirect()->route($this->routePrefix() . '.plagiarism.result', $check->id)
                ->with('status', 'Export PDF sedang diproses di background. Silakan tunggu beberapa saat lalu klik export lagi.');
        }

        $isDocx = strtolower(pathinfo($check->document->file_path ?? $check->document->original_filename, PATHINFO_EXTENSION)) === 'docx';
        $highlightedText = $isDocx ? '' : $this->buildHighlightedText($check, $sourceIndexMap);

        $this->historyService->log(
            auth()->user(),
            'export',
            "Export hasil cek plagiarisme: \"{$check->document->title}\""
        );

        return $this->plagiarismExportService->buildExportResponse(
            $check,
            $highlightedText,
            $downloadName
        );
    }

    public function documentPdf(PlagiarismCheck $plagiarismCheck)
    {
        Gate::authorize('view', $plagiarismCheck);

        $plagiarismCheck->load(['document', 'highlights.source']);
        $filePath = \Illuminate\Support\Facades\Storage::disk('public')->path($plagiarismCheck->document->file_path);
        $renderer = app(\App\Services\DocumentPageRenderer::class);
        $pdfPath = $renderer->resolveSourcePdfWithoutShell(
            $filePath,
            $plagiarismCheck->document->id,
            $plagiarismCheck->highlights->all(),
        );

        if (!$pdfPath || !file_exists($pdfPath)) {
            // Fallback to original if it's already a PDF
            if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'pdf') {
                return response()->file($filePath);
            }
            abort(404, 'Gagal memuat atau mengonversi dokumen ke format PDF asli.');
        }

        return response()->file($pdfPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"'
        ]);
    }

    public function documentDocx(PlagiarismCheck $plagiarismCheck)
    {
        Gate::authorize('view', $plagiarismCheck);

        $filePath = Storage::disk('public')->path($plagiarismCheck->document->file_path);
        if (! is_file($filePath)) {
            abort(404, 'Dokumen asli tidak ditemukan.');
        }

        return response()->file($filePath, [
            'Content-Type' => $plagiarismCheck->document->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . addslashes($plagiarismCheck->document->original_filename) . '"',
        ]);
    }

    public function buildHighlightedText(PlagiarismCheck $check, array $sourceIndexMap = []): string
    {
        $content = str_replace(["\r\n", "\r"], "\n", (string) ($check->document->content ?? ''));
        if (trim($content) === '') {
            return '';
        }

        $highlights = $check->highlights->filter(fn($h) => mb_strlen(trim($h->original_text)) >= 10);

        $positionHighlights = $highlights->filter(fn($h) => ($h->end_position ?? 0) > ($h->start_position ?? 0));

        if ($positionHighlights->isNotEmpty()) {
            $text = $this->buildPositionHighlights($content, $positionHighlights, $sourceIndexMap);
        } else {
            $text = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
            $sorted = $highlights->sortByDesc(fn($h) => mb_strlen($h->original_text));

            foreach ($sorted as $highlight) {
                $rawOriginal = $highlight->original_text;
                $needle = htmlspecialchars($rawOriginal, ENT_QUOTES, 'UTF-8');
                if ($needle === '') continue;

                $sourceId = $highlight->plagiarism_source_id;
                $tIndex = $sourceIndexMap[$sourceId] ?? '*';
                $color = $this->highlightColor($highlight, $tIndex);

                $label = htmlspecialchars((string) ($highlight->source->source_label ?? ''), ENT_QUOTES, 'UTF-8');
                $badge = "<sup class=\"t-badge\" style=\"background-color: {$color};\" title=\"{$label} ({$highlight->match_percentage}%)\">{$tIndex}</sup>";
                $replacement = "<mark class=\"t-highlight\" data-source-id=\"{$sourceId}\" data-source-index=\"{$tIndex}\" data-source-color=\"{$color}\" style=\"background-color: {$color}33; border-bottom: 2px solid {$color};\">{$badge}{$needle}</mark>";

                $text = str_replace($needle, $replacement, $text);
            }
        }

        return $this->formatHighlightedDocument($text);
    }

    private function buildPositionHighlights(string $content, $highlights, array $sourceIndexMap): string
    {
        $sorted = $highlights->sortByDesc('start_position')->values();
        $segments = [];
        $cursor = mb_strlen($content);

        foreach ($sorted as $highlight) {
            $start = (int) $highlight->start_position;
            $end = (int) $highlight->end_position;

            if ($end <= $start || $start >= $cursor) {
                continue;
            }

            if ($cursor > $end) {
                $segments[] = [
                    'type' => 'text',
                    'content' => mb_substr($content, $end, $cursor - $end),
                ];
            }

            $segments[] = [
                'type' => 'mark',
                'content' => mb_substr($content, $start, $end - $start),
                'source_id' => $highlight->plagiarism_source_id,
                'color' => $highlight->source?->color_code ?? '#FDE68A',
                'label' => $highlight->source->source_label ?? '',
                'percentage' => $highlight->match_percentage,
            ];

            $cursor = $start;
        }

        if ($cursor > 0) {
            $segments[] = [
                'type' => 'text',
                'content' => mb_substr($content, 0, $cursor),
            ];
        }

        $segments = array_reverse($segments);
        $html = '';

        foreach ($segments as $segment) {
            if ($segment['type'] === 'text') {
                $html .= htmlspecialchars($segment['content'], ENT_QUOTES, 'UTF-8');
                continue;
            }

            $tIndex = $sourceIndexMap[$segment['source_id']] ?? '*';
            $color = $segment['color'];
            $label = htmlspecialchars($segment['label'], ENT_QUOTES, 'UTF-8');
            $badge = "<sup class=\"t-badge\" style=\"background-color: {$color};\" title=\"{$label} ({$segment['percentage']}%)\">{$tIndex}</sup>";
            $html .= "<mark class=\"t-highlight\" data-source-id=\"{$segment['source_id']}\" data-source-index=\"{$tIndex}\" data-source-color=\"{$color}\" style=\"background-color: {$color}33; border-bottom: 2px solid {$color};\">{$badge}" . htmlspecialchars($segment['content'], ENT_QUOTES, 'UTF-8') . '</mark>';
        }

        return $html;
    }

    private function formatHighlightedDocument(string $html): string
    {
        $lines = explode("\n", $html);
        $blocks = [];
        $paragraph = [];

        $flushParagraph = function () use (&$blocks, &$paragraph): void {
            if ($paragraph === []) {
                return;
            }

            $content = trim(implode('<br>', $paragraph));
            if ($content !== '') {
                $blocks[] = '<p class="doc-paragraph">' . $content . '</p>';
            }
            $paragraph = [];
        };

        foreach ($lines as $line) {
            $trimmed = trim(strip_tags($line));
            if ($trimmed === '') {
                $flushParagraph();
                continue;
            }

            if (preg_match('/^(BAB\s+[IVXLCDM0-9]+\b.*|ABSTRAK|KATA PENGANTAR|DAFTAR ISI|DAFTAR PUSTAKA|LAMPIRAN)$/iu', $trimmed)) {
                $flushParagraph();
                $blocks[] = '<div class="doc-heading doc-heading-1">' . $line . '</div>';
                continue;
            }

            if (preg_match('/^[A-Z]\.(?:\s+|$)/u', $trimmed)) {
                $flushParagraph();
                $blocks[] = '<div class="doc-heading doc-heading-2">' . $line . '</div>';
                continue;
            }

            if (preg_match('/^(?:\d+\.|\d+(?:\.\d+)+)(?:\s+|$)/u', $trimmed)) {
                $flushParagraph();
                $blocks[] = '<div class="doc-heading doc-heading-3">' . $line . '</div>';
                continue;
            }

            $paragraph[] = $line;
        }

        $flushParagraph();

        return implode("\n", $blocks);
    }

    private function highlightColor($highlight, $sourceIndex): string
    {
        $color = (string) ($highlight->color_code ?? $highlight->source?->color_code ?? '');
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            return $color;
        }

        $palette = ['#FCA5A5', '#93C5FD', '#86EFAC', '#FCD34D', '#C4B5FD', '#5EEAD4', '#F9A8D4', '#FDBA74'];
        $index = is_numeric($sourceIndex) ? max(1, (int) $sourceIndex) - 1 : 0;

        return $palette[$index % count($palette)];
    }

    private function routePrefix(): string
    {
        return str_starts_with(request()->route()?->getName() ?? '', 'admin.') ? 'admin' : 'user';
    }

    /** @return array{layout: string, routePrefix: string} */
    private function viewContext(): array
    {
        $prefix = $this->routePrefix();

        return [
            'layout' => $prefix === 'admin' ? 'layouts.admin' : 'layouts.user',
            'routePrefix' => $prefix,
        ];
    }
}
