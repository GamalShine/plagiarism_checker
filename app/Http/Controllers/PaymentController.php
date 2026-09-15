<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPlagiarismCheck;
use App\Models\Document;
use App\Models\Payment;
use App\Models\PlagiarismCheck;
use App\Services\DokuService;
use App\Services\HistoryService;
use App\Services\PlagiarismService;
use App\Services\PlagiarismExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct(
        private PlagiarismService $plagiarismService,
        private HistoryService $historyService,
        private DokuService $dokuService,
        private PlagiarismExportService $plagiarismExportService,
    ) {}

    public function packageCheckout(string $packageKey)
    {
        $package = config("plans.{$packageKey}");

        abort_unless($package, 404);

        $pendingPayment = Payment::query()
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->whereNotNull('package_key')
            ->latest('id')
            ->first();

        $lockedPackageKey = $pendingPayment?->package_key ?: auth()->user()->pending_package_key;

        if ($lockedPackageKey && $lockedPackageKey !== $packageKey) {
            return redirect()->route('user.payment.package', $lockedPackageKey);
        }

        return view('payment.package', compact('package', 'packageKey'));
    }

    public function initiatePackage(string $packageKey)
    {
        $package = config("plans.{$packageKey}");

        abort_unless($package, 404);

        $existingPayment = Payment::query()
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->where('package_key', $packageKey)
            ->latest('id')
            ->first();

        if ($existingPayment?->snap_token) {
            return redirect()->away($existingPayment->snap_token);
        }

        $payment = Payment::create([
            'user_id' => auth()->id(),
            'order_id' => 'PKG-' . strtoupper(Str::random(10)) . '-' . time(),
            'amount' => $package['amount'],
            'package_key' => $packageKey,
            'package_name' => $package['name'],
            'package_quota' => $package['quota'],
            'package_days' => $package['days'],
            'status' => 'pending',
        ]);

        auth()->user()->update(['pending_package_key' => $packageKey]);

        $result = $this->dokuService->createPayment($payment);

        if (! $result['success']) {
            return back()->with('error', 'Gagal menghubungkan ke DOKU: ' . ($result['message'] ?? 'Periksa konfigurasi.'));
        }

        return redirect()->away($result['url']);
    }

    /**
     * Step 1 – Upload file, simpan sementara, request payment link DOKU, redirect ke checkout DOKU.
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'file'      => 'required|file|mimes:pdf,docx,txt',
            'sources'   => 'required|array|min:1',
            'sources.*' => 'in:web,google_scholar,elsevier,openalex,crossref,crossref_posted,publications',
        ]);

        $user = auth()->user();
        $file = $request->file('file');
        $chapters = [];

        $tempPath = $file->store('temp_payments', 'local');

        $payment = Payment::create([
            'user_id'           => $user->id,
            'order_id'          => 'PC-' . strtoupper(Str::random(10)) . '-' . time(),
            'temp_file_path'    => $tempPath,
            'original_filename' => $file->getClientOriginalName(),
            'sources'           => $request->input('sources'),
            'amount'            => config('doku.check_price', 10000),
            'status'            => 'pending',
            'chapters'          => $chapters,
        ]);

        $result = $this->dokuService->createPayment($payment);

        if (! $result['success']) {
            return redirect()
                ->route('user.plagiarism.index')
                ->withErrors(['payment' => 'Gagal menghubungkan ke DOKU: ' . ($result['message'] ?? 'Periksa konfigurasi.')]);
        }

        return redirect()->away($result['url']);
    }

    /**
     * Step 2 – User klik "Sudah Bayar".
     * Langsung mark paid dan jalankan plagiarism check.
     */
    public function confirm(Request $request, string $orderId)
    {
        $payment = Payment::where('order_id', $orderId)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->firstOrFail();

        $this->markPaidAndQueue($payment);
        $payment->refresh();

        if (! $payment->plagiarism_check_id) {
            return redirect()
                ->route('user.plagiarism.index')
                ->withErrors(['payment' => 'Terjadi kesalahan saat memproses dokumen. Silakan coba lagi.']);
        }

        return redirect()
            ->route('user.payment.finish', $payment->order_id)
            ->with('success', 'Pembayaran dikonfirmasi! Proses pengecekan plagiarisme telah dimulai.');
    }

    /**
     * DOKU webhook notification.
     */
    public function notification(Request $request)
    {
        $rawBody = $request->getContent();
        $payload = json_decode($rawBody, true) ?: $request->all();

        Log::info('DOKU Notification Received: ', ['payload' => $payload]);

        $invoiceNumber = $payload['order']['invoice_number'] ?? null;
        if (! $invoiceNumber) {
            return response()->json(['status' => 'invalid_invoice'], 400);
        }

        $payment = Payment::where('order_id', $invoiceNumber)->first();
        if (! $payment) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $transactionStatus = $payload['transaction']['status'] ?? null;

        if ($transactionStatus === 'SUCCESS' || ($payload['order']['status'] ?? '') === 'SUCCESS') {
            $this->markPaidAndQueue($payment, $payload);

            return response()->json(['status' => 'SUCCESS']);
        }

        if ($transactionStatus === 'FAILED') {
            $payment->update([
                'status' => 'failed',
                'midtrans_payload' => $payload,
            ]);
        }

        return response()->json(['status' => 'OK']);
    }

    public function finish(string $orderId)
    {
        $payment = Payment::where('order_id', $orderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($payment->isPending()) {
            $this->markPaidAndQueue($payment);
        }

        return redirect()->route('user.plagiarism.index', [
            'payment' => $payment->order_id,
        ]);
    }

    public function error(string $orderId)
    {
        return redirect()
            ->route('user.plagiarism.index')
            ->withErrors(['payment' => 'Pembayaran gagal atau dibatalkan. Silakan coba lagi.']);
    }

    public function pending(string $orderId)
    {
        $payment = Payment::where('order_id', $orderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('payment.waiting', [
            'payment' => $payment,
            'layout'  => 'layouts.user',
        ]);
    }

    public function guestFinish(string $token)
    {
        $payment = Payment::where('guest_token', $token)->firstOrFail();

        if ($payment->isPending()) {
            $this->markPaidAndQueue($payment);
        }

        return redirect()->route('free.check.index', ['guest_token' => $token]);
    }

    public function guestWaiting(string $token)
    {
        Payment::where('guest_token', $token)->firstOrFail();

        return view('guest.payment_waiting', compact('token'));
    }

    public function guestStatus(string $token)
    {
        $payment = Payment::where('guest_token', $token)->firstOrFail();
        $check = $payment->plagiarismCheck()
            ->with([
                'document',
                'sources' => fn ($query) => $query
                    ->with('highlights')
                    ->orderByDesc('similarity_score'),
            ])
            ->first();

        $visibleSources = $check?->status === 'completed'
            ? $check->sources->values()
            : collect();

        $documentSentenceCount = 0;
        if ($check?->document?->content) {
            $documentSentenceCount = count(array_filter(
                array_map('trim', preg_split('/[.!?]+/u', trim($check->document->content))),
                fn ($sentence) => mb_strlen($sentence) > 20,
            ));
        }

        $guestSources = $check?->status === 'completed'
            ? $visibleSources->map(function ($source) {
                return [
                    'title' => $source->title,
                    'source_label' => $source->source_label,
                    'similarity_score' => $source->similarity_score,
                    'percentage' => $source->turnitin_percentage,
                    'matched_words' => $source->matched_words,
                    'color_code' => $source->color_code ?? '#94a3b8',
                    'url' => $source->url,
                ];
            })->filter(fn ($source) => $source['matched_words'] > 0 && $source['matched_words'] < 1000)->values()
            : collect();

        $hiddenSourceCount = max(0, ($check?->sources?->count() ?? 0) - $guestSources->count());

        return response()->json([
            'status' => $payment->status,
            'plagiarism_status' => $check?->status,
            'similarity' => $check?->total_similarity,
            'total_sentences' => $documentSentenceCount,
            'matched_sentences' => $check?->matched_sentences,
            'sources' => $check?->status === 'completed'
                ? $guestSources
                : [],
            'hidden_sources_count' => $hiddenSourceCount,
            'highlights_count' => $check?->status === 'completed' ? $check->highlights()->count() : 0,
            'export_url' => $check?->status === 'completed'
                ? route('guest.payment.export', $token)
                : null,
            'highlights' => $check?->status === 'completed'
                ? $check->highlights()->with('source:id,source_label')->latest()->get()->map(fn ($highlight) => [
                    'text' => $highlight->original_text,
                    'percentage' => $highlight->match_percentage,
                    'source_label' => $highlight->source?->source_label ?? 'Sumber',
                ])->values()
                : [],
            'result_url' => $check?->status === 'completed'
                ? route('guest.payment.result', $token)
                : null,
        ]);
    }

    public function guestResult(string $token)
    {
        $payment = Payment::where('guest_token', $token)->firstOrFail();
        $check = $payment->plagiarismCheck()
            ->with(['document', 'sources' => fn ($q) => $q->orderBy('similarity_score', 'desc'), 'highlights.source'])
            ->firstOrFail();

        abort_unless($check->status === 'completed', 404);

        $sourceIndexMap = [];
        foreach ($check->sources as $index => $source) {
            $source->turnitin_index = $index + 1;
            $sourceIndexMap[$source->id] = $index + 1;
        }

        $highlightedText = function_exists('shell_exec')
            ? app(PlagiarismController::class)->buildHighlightedText($check, $sourceIndexMap)
            : '';

        return view('guest.result', [
            'link' => null,
            'check' => $check,
            'highlightedText' => $highlightedText,
        ]);
    }

    public function guestExport(string $token)
    {
        $payment = Payment::where('guest_token', $token)->firstOrFail();
        $check = $payment->plagiarismCheck()
            ->with(['document', 'user', 'sources' => fn ($q) => $q->orderBy('similarity_score', 'desc'), 'highlights.source'])
            ->firstOrFail();

        abort_unless($check->status === 'completed', 404);

        $sourceIndexMap = [];
        foreach ($check->sources as $index => $source) {
            $source->turnitin_index = $index + 1;
            $sourceIndexMap[$source->id] = $index + 1;
        }

        $highlightedText = function_exists('shell_exec')
            ? app(PlagiarismController::class)->buildHighlightedText($check, $sourceIndexMap)
            : '';

        return $this->plagiarismExportService->buildExportResponse(
            $check,
            $highlightedText,
            'plagiarism_report_guest_' . $check->id . '.pdf',
        );
    }

    public function guestError(string $token)
    {
        return redirect()->route('free.check.index')->withErrors([
            'payment' => 'Pembayaran guest gagal atau dibatalkan. Silakan coba lagi.',
        ]);
    }

    /**
     * AJAX status poll.
     */
    public function status(string $orderId)
    {
        $payment = Payment::where('order_id', $orderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $check = $payment->plagiarismCheck;

        return response()->json([
            'status'              => $payment->status,
            'plagiarism_check_id' => $payment->plagiarism_check_id,
            'plagiarism_status'   => $check?->status,
            'result_url'          => $check?->status === 'completed'
                ? route('user.plagiarism.result', $payment->plagiarism_check_id)
                : null,
        ]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function queuePlagiarismCheck(Payment $payment): void
    {
        try {
            $user = $payment->user;

            $content = Storage::disk('local')->get($payment->temp_file_path);
            $safeName = Str::uuid() . '.' . pathinfo($payment->original_filename, PATHINFO_EXTENSION);
            $permanentPath = 'documents/' . $safeName;
            Storage::disk('public')->put($permanentPath, $content);

            $fullPath = Storage::disk('public')->path($permanentPath);
            $mime = mime_content_type($fullPath);

            $textContent = $this->plagiarismService->extractTextFromFile($fullPath, $mime);

            if (empty(trim($textContent))) {
                Log::warning("Empty text extracted after payment {$payment->order_id}");
                return;
            }

            $document = Document::create([
                'user_id' => $user->id,
                'title' => pathinfo($payment->original_filename, PATHINFO_FILENAME),
                'file_path' => $permanentPath,
                'original_filename' => $payment->original_filename,
                'type' => 'plagiarism',
                'status' => 'processing',
                'content' => $textContent,
                'file_size' => strlen($content),
                'mime_type' => $mime,
            ]);

            $check = PlagiarismCheck::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'status' => 'processing',
                'sources_checked' => $payment->sources,
                'chapters' => $payment->chapters ?? [],
            ]);

            $payment->update(['plagiarism_check_id' => $check->id]);
            Storage::disk('local')->delete($payment->temp_file_path);

            ProcessPlagiarismCheck::dispatch($check->id, $payment->chapters ?? []);
        } catch (\Exception $e) {
            $payment->update(['status' => 'failed']);
            Log::error("queuePlagiarismCheck failed for payment {$payment->order_id}: " . $e->getMessage());
        }
    }

    private function markPaidAndQueue(Payment $payment, array $payload = []): void
    {
        $payment->update([
            'status' => 'paid',
            'payment_type' => $payload['channel']['id'] ?? $payload['payment']['payment_method_type'] ?? $payment->payment_type ?? 'doku',
            'midtrans_payload' => $payload ?: $payment->midtrans_payload,
            'paid_at' => $payment->paid_at ?? now(),
        ]);

        if ($payment->package_key) {
            $this->grantPackage($payment);
            return;
        }

        if (empty($payment->plagiarism_check_id)) {
            $this->queuePlagiarismCheck($payment);
        }
    }

    private function grantPackage(Payment $payment): void
    {
        $user = $payment->user;
        $startsAt = $user->hasActivePackage() && $user->package_expires_at
            ? $user->package_expires_at
            : now();

        $user->update([
            'role' => 'member',
            'package_key' => $payment->package_key,
            'package_credits' => (int) $payment->package_quota,
            'package_expires_at' => $startsAt->copy()->addDays((int) $payment->package_days),
            'pending_package_key' => null,
        ]);
    }
}
