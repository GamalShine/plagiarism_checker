<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Payment;
use App\Models\PlagiarismCheck;
use App\Models\User;
use App\Jobs\ProcessPlagiarismCheck;
use App\Services\PageContentFetcher;
use App\Services\PlagiarismExportService;
use App\Services\PlagiarismService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FreeCheckController extends Controller
{
    public function __construct(
        private PlagiarismService $plagiarismService,
        private PageContentFetcher $pageContentFetcher,
        private PlagiarismExportService $plagiarismExportService,
    ) {}

    public function index(): View
    {
        return view('plagiarism.index', [
            'layout' => 'layouts.landing',
            'routePrefix' => 'free.plagiarism',
            'publicMode' => true,
            'settings' => [
                'default_sources' => ['web', 'google_scholar', 'elsevier', 'openalex', 'crossref', 'crossref_posted', 'publications'],
            ],
            'guestToken' => request()->query('guest_token'),
        ]);
    }

    public function extractChapters(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:pdf,docx,txt']);

        $file = $request->file('file');
        $path = $file->store('temp_documents', 'public');
        $fullPath = \Storage::disk('public')->path($path);
        $text = $this->plagiarismService->extractTextFromFile($fullPath, $file->getMimeType());
        @unlink($fullPath);

        preg_match_all('/\b(?:BAB\s+[IVXLCDM0-9]+|ABSTRAK|KATA PENGANTAR|DAFTAR ISI|DAFTAR PUSTAKA|LAMPIRAN)\b/ui', $text, $matches);
        $chapters = [];
        foreach (array_map('strtoupper', array_map('trim', $matches[0] ?? [])) as $chapter) {
            $key = strtolower(str_replace(' ', '_', $chapter));
            if (str_starts_with($chapter, 'BAB')) {
                preg_match('/BAB\s+([IVXLCDM0-9]+)/i', $chapter, $match);
                $key = (string) ($match[1] ?? '');
            }
            if ($key && ! in_array($key, $chapters, true)) {
                $chapters[] = $key;
            }
        }

        return response()->json(['chapters' => $chapters]);
    }

    public function guestFileCheck(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email:rfc|max:190',
            'file' => 'required|file|mimes:pdf,docx,txt|max:51200',
            'sources' => 'required|array|min:1',
            'sources.*' => 'in:web,google_scholar,elsevier,openalex,crossref,crossref_posted,publications',
        ]);

        $user = auth()->user() ?: User::firstOrCreate(
            ['email' => $request->string('email')->toString()],
            [
                'name' => $request->string('name')->toString(),
                'password' => Str::random(48),
            ],
        );

        $tempPath = $request->file('file')->store('temp_payments', 'local');
        $token = Str::random(64);
        $payment = Payment::create([
            'user_id' => $user->id,
            'guest_token' => $token,
            'order_id' => 'GPC-' . strtoupper(Str::random(10)) . '-' . time(),
            'temp_file_path' => $tempPath,
            'original_filename' => $request->file('file')->getClientOriginalName(),
            'sources' => $request->input('sources'),
            'chapters' => [],
            'amount' => config('doku.check_price', 10000),
            'status' => 'pending',
        ]);

        $result = app(\App\Services\DokuService::class)->createPayment($payment);
        if (! $result['success']) {
            Storage::disk('local')->delete($tempPath);
            $payment->delete();

            return back()->withErrors(['payment' => 'Gagal membuat pembayaran: ' . ($result['message'] ?? 'Periksa konfigurasi DOKU.')]);
        }

        return redirect()->away($result['url']);
    }

    public function check(Request $request)
    {
        $request->validate([
            'text' => 'required|string|min:50',
            'sources' => 'nullable|array',
            'sources.*' => 'in:web,google_scholar,elsevier,openalex,crossref,crossref_posted,publications',
        ], [
            'text.required' => 'Masukkan teks yang ingin diperiksa.',
            'text.min' => 'Teks terlalu pendek. Masukkan minimal 50 karakter.',
        ]);

        $rawText = trim($request->input('text'));
        $words = preg_split('/\s+/u', $rawText, -1, PREG_SPLIT_NO_EMPTY);
        $wordCount = count($words);

        if ($wordCount > 5000) {
            return response()->json([
                'success' => false,
                'message' => 'Batas teks coba gratis adalah maksimal 5.000 kata (Anda memasukkan ' . number_format($wordCount) . ' kata).',
            ], 422);
        }

        // Get authenticated user jika ada (otherwise use system user untuk guest)
        $authenticatedUser = auth()->user();
        $systemUser = $authenticatedUser ?? (User::where('role', 'admin')->first() ?? User::first());
        
        if (!$systemUser) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan sedang dalam pemeliharaan.',
            ], 500);
        }

        // Default sources dengan 'elsevier' included (sama dengan user/admin check)
        $defaultSources = ['web', 'google_scholar', 'elsevier', 'openalex', 'crossref', 'crossref_posted', 'publications'];
        $selectedSources = $request->input('sources') ?: $defaultSources;
        if (empty($selectedSources)) {
            $selectedSources = $defaultSources;
        }

        $title = 'Cek Gratis - ' . Str::limit(strip_tags($rawText), 35);

        // Document owner: authenticated user (jika ada), atau system user (jika guest)
        $documentOwnerId = $authenticatedUser?->id ?? $systemUser->id;

        $document = Document::create([
            'user_id' => $documentOwnerId,
            'title' => $title,
            'type' => 'plagiarism',
            'status' => 'processing',
            'content' => $rawText,
            'file_size' => strlen($rawText),
            'mime_type' => 'text/plain',
        ]);

        $check = PlagiarismCheck::create([
            'document_id' => $document->id,
            'user_id' => $documentOwnerId,
            'status' => 'processing',
            'sources_checked' => $selectedSources,
        ]);

        ProcessPlagiarismCheck::dispatch($check->id);

        return response()->json([
            'success' => true,
            'data' => [
                'status' => 'processing',
                'check_id' => $check->id,
                'total_words' => $wordCount,
            ],
        ], 202);
    }

    public function status(PlagiarismCheck $plagiarismCheck)
    {
        if ($plagiarismCheck->document?->user_id !== (auth()->id() ?? $plagiarismCheck->user_id)) {
            abort(404);
        }

        $check = $plagiarismCheck->load(['sources' => fn ($query) => $query->orderBy('similarity_score', 'desc'), 'highlights.source']);
        if ($check->status !== 'completed') {
            return response()->json([
                'success' => true,
                'data' => ['status' => $check->status, 'error_message' => $check->error_message],
            ]);
        }

        $sourceIndexMap = [];
        foreach ($check->sources as $index => $source) {
            $sourceIndexMap[$source->id] = $index + 1;
        }

        $sources = $check->sources->filter(fn ($source) => $source->matched_words < 1000 && $source->matched_words > 0)->values()->map(fn ($source) => [
            'index' => $sourceIndexMap[$source->id] ?? '*',
            'color' => $source->color_code ?? '#ff0000',
            'title' => $source->title ?: $source->source_label,
            'source_label' => $source->source_label,
            'matched_words' => $source->matched_words,
            'percentage' => $source->turnitin_percentage,
        ]);

        $highlights = $check->highlights->map(fn ($highlight) => [
            'index' => $sourceIndexMap[$highlight->plagiarism_source_id] ?? '*',
            'color' => $highlight->color_code ?? $highlight->source?->color_code ?? '#ff0000',
            'source_label' => $highlight->source?->source_label ?? 'Sumber',
            'match_percentage' => $highlight->match_percentage,
            'original_text' => $highlight->original_text,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'status' => 'completed',
                'total_similarity' => (float) $check->total_similarity,
                'status_label' => $check->similarity_label,
                'highlights_count' => $check->highlights->count(),
                'sources_count' => $check->sources->count(),
                'total_words' => $check->total_words,
                'sources' => $sources,
                'highlights' => $highlights,
                'export_url' => route('free.check.export', $check),
            ],
        ]);
    }

    public function export(PlagiarismCheck $plagiarismCheck)
    {
        abort_unless($plagiarismCheck->status === 'completed', 404);

        $check = $plagiarismCheck->load([
            'document',
            'sources' => fn ($query) => $query->orderByDesc('similarity_score'),
            'highlights.source',
        ]);

        $sourceIndexMap = [];
        foreach ($check->sources as $index => $source) {
            $source->turnitin_index = $index + 1;
            $sourceIndexMap[$source->id] = $index + 1;
        }

        $highlightedText = function_exists('shell_exec')
            ? app(\App\Http\Controllers\PlagiarismController::class)->buildHighlightedText($check, $sourceIndexMap)
            : '';

        return $this->plagiarismExportService->buildExportResponse(
            $check,
            $highlightedText,
            'plagiarism_report_guest_' . $check->id . '.pdf',
            true,
        );
    }

    /**
     * Ambil konten teks dari URL publik atau Google Drive link (Google Docs / Drive sharing)
     */
    public function fetchUrl(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ], [
            'url.required' => 'Masukkan link/URL yang valid.',
            'url.url' => 'Format URL tidak valid. Pastikan diawali http:// atau https://',
        ]);

        $url = trim($request->input('url'));

        try {
            $extractedText = '';
            $sourceType = 'web';

            // Cek apakah Google Drive / Google Docs URL
            if (str_contains($url, 'drive.google.com') || str_contains($url, 'docs.google.com')) {
                $sourceType = 'gdrive';
                $extractedText = $this->fetchFromGoogleDriveOrDocs($url);
            } else {
                $extractedText = $this->pageContentFetcher->fetch($url);
            }

            $extractedText = trim($extractedText);

            if (empty($extractedText)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengambil konten teks dari URL tersebut. Pastikan akses tautan bersifat publik/siapa saja yang memiliki link dapat melihat.',
                ], 422);
            }

            // Hitung kata
            $words = preg_split('/\s+/u', $extractedText, -1, PREG_SPLIT_NO_EMPTY);
            $wordCount = count($words);

            return response()->json([
                'success' => true,
                'source_type' => $sourceType,
                'text' => $extractedText,
                'word_count' => $wordCount,
                'message' => 'Konten teks berhasil dimuat (' . number_format($wordCount) . ' kata).',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat URL: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function fetchFromGoogleDriveOrDocs(string $url): string
    {
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

        // 1. Pola Google Docs: https://docs.google.com/document/d/{DOC_ID}/...
        if (preg_match('/docs\.google\.com\/document\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            $docId = $matches[1];
            
            // Coba export format txt
            $exportTxtUrl = "https://docs.google.com/document/d/{$docId}/export?format=txt";
            $response = Http::timeout(10)->withoutVerifying()->withHeaders(['User-Agent' => $userAgent])->get($exportTxtUrl);
            if ($response->successful() && !empty(trim($response->body())) && !str_contains($response->body(), '<!DOCTYPE html>')) {
                return $response->body();
            }

            // Coba export format docx jika txt redirect atau restricted
            $exportDocxUrl = "https://docs.google.com/document/d/{$docId}/export?format=docx";
            $responseDocx = Http::timeout(15)->withoutVerifying()->withHeaders(['User-Agent' => $userAgent])->get($exportDocxUrl);
            if ($responseDocx->successful() && !empty($responseDocx->body()) && !str_contains(substr($responseDocx->body(), 0, 100), '<!DOCTYPE')) {
                $tempPath = tempnam(sys_get_temp_dir(), 'gdocs_') . '.docx';
                file_put_contents($tempPath, $responseDocx->body());
                $text = $this->plagiarismService->extractTextFromFile($tempPath, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
                @unlink($tempPath);
                if (!empty(trim($text))) {
                    return $text;
                }
            }

            // Coba export format html
            $exportHtmlUrl = "https://docs.google.com/document/d/{$docId}/export?format=html";
            $responseHtml = Http::timeout(10)->withoutVerifying()->withHeaders(['User-Agent' => $userAgent])->get($exportHtmlUrl);
            if ($responseHtml->successful() && !empty(trim($responseHtml->body()))) {
                $html = $responseHtml->body();
                $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
                $html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html);
                $html = strip_tags($html);
                $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $text = preg_replace('/\s+/u', ' ', $html);
                if (!empty(trim($text))) {
                    return trim($text);
                }
            }
        }

        // 2. Pola Google Drive File: https://drive.google.com/file/d/{FILE_ID}/... atau id={FILE_ID}
        $fileId = null;
        if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            $fileId = $matches[1];
        } elseif (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
            $fileId = $matches[1];
        }

        if ($fileId) {
            // Coba direct download file
            $directDownloadUrl = "https://drive.google.com/uc?export=download&id={$fileId}&confirm=t";
            $response = Http::timeout(15)->withoutVerifying()->withHeaders(['User-Agent' => $userAgent])->get($directDownloadUrl);

            if ($response->successful() && !empty($response->body())) {
                $body = $response->body();
                
                // Jika direct download mengembalikan html virus warning/confirm page
                if (str_contains($body, 'confirm=') && preg_match('/confirm=([a-zA-Z0-9_-]+)/', $body, $cMatches)) {
                    $confirmCode = $cMatches[1];
                    $retryUrl = "https://drive.google.com/uc?export=download&id={$fileId}&confirm={$confirmCode}";
                    $retryResponse = Http::timeout(15)->withoutVerifying()->withHeaders(['User-Agent' => $userAgent])->get($retryUrl);
                    if ($retryResponse->successful()) {
                        $body = $retryResponse->body();
                    }
                }

                $tempPath = tempnam(sys_get_temp_dir(), 'gdrive_') . '.tmp';
                file_put_contents($tempPath, $body);

                $mime = @mime_content_type($tempPath) ?: 'text/plain';
                $text = $this->plagiarismService->extractTextFromFile($tempPath, $mime);
                @unlink($tempPath);

                if (!empty(trim($text))) {
                    return $text;
                }
            }
        }

        // Fallback jika berupa viewer page umum
        return $this->pageContentFetcher->fetch($url);
    }
}
