<?php

namespace App\Http\Controllers;

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

    public function export(PlagiarismCheck $plagiarismCheck)
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

        $highlightedText = $this->buildHighlightedText($check, $sourceIndexMap);

        $this->historyService->log(
            auth()->user(),
            'export',
            "Export hasil cek plagiarisme: \"{$check->document->title}\""
        );

        return $this->plagiarismExportService->buildExportResponse(
            $check,
            $highlightedText,
            'plagiarism_report_' . $check->id . '.pdf'
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
        $content = $check->document->content ?? '';
        $highlights = $check->highlights->filter(fn($h) => mb_strlen(trim($h->original_text)) >= 10);

        $positionHighlights = $highlights->filter(fn($h) => ($h->end_position ?? 0) > ($h->start_position ?? 0));

        if ($positionHighlights->isNotEmpty()) {
            $text = $this->buildPositionHighlights($content, $positionHighlights, $sourceIndexMap);
        } else {
            $text = htmlspecialchars($content);
            $sorted = $highlights->sortByDesc(fn($h) => mb_strlen($h->original_text));

            foreach ($sorted as $highlight) {
                $rawOriginal = $highlight->original_text;
                $needle = htmlspecialchars($rawOriginal);
                if ($needle === '') continue;

                $sourceId = $highlight->plagiarism_source_id;
                $tIndex = $sourceIndexMap[$sourceId] ?? '*';
                $color = 'transparent';

                $badge = "<sup class=\"t-badge\" style=\"background-color: {$color};\" title=\"" . htmlspecialchars($highlight->source->source_label ?? '') . " ({$highlight->match_percentage}%)\">{$tIndex}</sup>";
                $replacement = "<mark class=\"t-highlight\" data-source-id=\"{$sourceId}\" data-source-index=\"{$tIndex}\" data-source-color=\"{$color}\" style=\"background-color: {$color}33; border-bottom: 2px solid {$color};\">{$badge}{$needle}</mark>";

                $text = str_replace($needle, $replacement, $text);
            }
        }

        // Pertahankan struktur paragraf dan indentasi naskah dokumen asli
        $paragraphs = preg_split('/\r\n\r\n|\n\n|\r\r/', $text);
        if (count($paragraphs) > 1) {
            $formattedParagraphs = [];
            foreach ($paragraphs as $p) {
                $pTrim = trim($p);
                if ($pTrim === '') continue;
                $formattedParagraphs[] = '<p>' . nl2br($pTrim) . '</p>';
            }
            $text = implode("\n", $formattedParagraphs);
        } else {
            $text = nl2br($text);
        }

        // Auto-format headings & next page break (BAB / Pemisah Halaman)
        $text = preg_replace(
            '/^(<mark[^>]*>)?(BAB\s+[IVXLCDM0-9]+.*?)(<\/mark>)?(\s|<br\s*\/?>)*$/mi',
            '<div class="doc-page-break"></div><div style="text-align: center; font-weight: bold; margin-top: 2rem; margin-bottom: 1.5rem; font-size: 18px; text-transform: uppercase;">$1$2$3</div>',
            $text
        );

        $text = preg_replace(
            '/^(<mark[^>]*>)?(ABSTRAK|KATA PENGANTAR|DAFTAR ISI|DAFTAR PUSTAKA|LAMPIRAN)(<\/mark>)?(\s|<br\s*\/?>)*$/mi',
            '<div class="doc-page-break"></div><div style="text-align: center; font-weight: bold; margin-top: 2rem; margin-bottom: 1.5rem; font-size: 18px; text-transform: uppercase;">$1$2$3</div>',
            $text
        );

        return $text;
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
                'color' => 'transparent',
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
                $html .= htmlspecialchars($segment['content']);
                continue;
            }

            $tIndex = $sourceIndexMap[$segment['source_id']] ?? '*';
            $color = 'transparent';
            $badge = "<sup class=\"t-badge\" style=\"background-color: {$color};\" title=\"" . htmlspecialchars($segment['label']) . " ({$segment['percentage']}%)\">{$tIndex}</sup>";
            $html .= "<mark class=\"t-highlight\" data-source-id=\"{$segment['source_id']}\" data-source-index=\"{$tIndex}\" data-source-color=\"{$color}\" style=\"background-color: {$color}66;\">{$badge}" . htmlspecialchars($segment['content']) . '</mark>';
        }

        return nl2br($html);
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
