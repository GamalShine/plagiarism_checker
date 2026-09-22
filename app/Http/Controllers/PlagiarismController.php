<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\PlagiarismCheck;
use App\Services\HistoryService;
use App\Services\PlagiarismExportService;
use App\Services\PlagiarismService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $request->validate([
            'file'    => 'required|file|mimes:pdf,docx,txt',
            'sources' => 'required|array|min:1',
            'sources.*' => 'in:web,google_scholar,elsevier,openalex,crossref,crossref_posted,publications',
            'chapters' => 'nullable|array',
            'chapters.*' => 'in:abstrak,kata_pengantar,1,2,3,4,5,6,7,8,9,10,daftar_pustaka,lampiran',
        ]);

        $user = auth()->user();
        $file = $request->file('file');

        // Store file
        $path = $file->store('documents', 'public');
        $originalName = $file->getClientOriginalName();

        // Extract text
        $fullPath = Storage::disk('public')->path($path);
        $chapters = $request->input('chapters', []);
        $content = $this->plagiarismService->extractTextFromFile($fullPath, $file->getMimeType(), $chapters);

        if (empty(trim($content))) {
            return back()->withErrors([
                'file' => 'Tidak dapat mengekstrak teks dari file. Upload dokumen asli (Word/PDF berisi teks), bukan laporan Turnitin atau scan gambar.',
            ]);
        }

        // Create document
        $document = Document::create([
            'user_id'           => $user->id,
            'title'             => pathinfo($originalName, PATHINFO_FILENAME),
            'file_path'         => $path,
            'original_filename' => $originalName,
            'type'              => 'plagiarism',
            'status'            => 'processing',
            'content'           => $content,
            'file_size'         => $file->getSize(),
            'mime_type'         => $file->getMimeType(),
        ]);

        // Run plagiarism check
        $check = $this->plagiarismService->check(
            $document,
            $request->input('sources'),
            $user->settings
        );

        $document->update(['status' => 'completed']);

        // Log history
        $this->historyService->logPlagiarismCheck(
            $user,
            $check->id,
            $document->title,
            $check->total_similarity,
            $request->input('sources')
        );

        return redirect()->route($this->routePrefix() . '.plagiarism.result', $check->id)
            ->with('success', 'Pengecekan plagiasi berhasil diselesaikan!');
    }

    public function result(PlagiarismCheck $plagiarismCheck): View
    {
        Gate::authorize('view', $plagiarismCheck);

        $check = $plagiarismCheck->load(['document', 'sources' => function ($query) {
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

        return view('plagiarism.result', array_merge(
            compact('check', 'highlightedText', 'sourceIndexMap'),
            $this->viewContext()
        ));
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
            "Export hasil cek plagiasi: \"{$check->document->title}\""
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

        $filePath = \Illuminate\Support\Facades\Storage::disk('public')->path($plagiarismCheck->document->file_path);
        $renderer = app(\App\Services\DocumentPageRenderer::class);
        $pdfPath = $renderer->resolveSourcePdf($filePath, $plagiarismCheck->document->id);

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
                $needle = htmlspecialchars($highlight->original_text);
                if ($needle === '') continue;

                $sourceId = $highlight->plagiarism_source_id;
                $tIndex = $sourceIndexMap[$sourceId] ?? '*';
                $color = $highlight->source->color_code ?? '#ff0000';

                $badge = "<sup class=\"t-badge\" style=\"background-color: {$color};\" title=\"" . htmlspecialchars($highlight->source->source_label ?? '') . " ({$highlight->match_percentage}%)\">{$tIndex}</sup>";
                $replacement = "<mark class=\"t-highlight\" style=\"background-color: {$color}66;\">{$badge}{$needle}</mark>";

                $text = str_replace($needle, $replacement, $text);
            }
        }

        $text = nl2br($text);

        // Auto-format headings to make the plain text look more like a real document
        $text = preg_replace(
            '/^(<mark[^>]*>)?(BAB\s+[IVXLCDM0-9]+.*?|ABSTRAK|KATA PENGANTAR|DAFTAR ISI|DAFTAR PUSTAKA|LAMPIRAN)(<\/mark>)?(\s|<br\s*\/?>)*$/mi',
            '<div style="text-align: center; font-weight: bold; margin-top: 2rem; margin-bottom: 1rem; text-transform: uppercase;">$1$2$3</div>',
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
                'color' => $highlight->source->color_code ?? '#ff0000',
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
            $color = $segment['color'];
            $badge = "<sup class=\"t-badge\" style=\"background-color: {$color};\" title=\"" . htmlspecialchars($segment['label']) . " ({$segment['percentage']}%)\">{$tIndex}</sup>";
            $html .= "<mark class=\"t-highlight\" style=\"background-color: {$color}66;\">{$badge}" . htmlspecialchars($segment['content']) . '</mark>';
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
