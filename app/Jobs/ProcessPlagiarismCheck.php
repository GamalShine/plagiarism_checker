<?php

namespace App\Jobs;

use App\Models\PlagiarismCheck;
use App\Models\History;
use App\Services\HistoryService;
use App\Services\PlagiarismService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessPlagiarismCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 1;
    public int $backoff = 0;

    public int $checkId;
    public array $chapters = [];

    public function __construct(int $checkId, array $chapters = [])
    {
        $this->checkId = $checkId;
        $this->chapters = $chapters;
        $this->onQueue('plagiarism');
    }

    public function handle(PlagiarismService $plagiarismService, HistoryService $historyService): void
    {
        try {
            $check = PlagiarismCheck::with(['document.user', 'document.user.settings'])->findOrFail($this->checkId);

            if (! $check->document) {
                $check->update([
                    'status' => 'failed',
                    'error_message' => 'Dokumen tidak ditemukan.',
                ]);

                return;
            }

            $user = $check->document->user;
            // Reload settings fresh to ensure not NULL and properly loaded
            $settings = $user->load('settings')->settings;

            $selectedChapters = (isset($this->chapters) && $this->chapters)
                ? $this->chapters
                : ($check->chapters ?? []);

            Log::info('Plagiarism section filter started', [
                'check_id' => $check->id,
                'selected_sections' => $selectedChapters,
                'selected_count' => count($selectedChapters),
            ]);

            $document = $check->document;
            $content = (string) $document->content;
            if ($document->file_path) {
                $filePath = Storage::disk('public')->path($document->file_path);
                $content = $plagiarismService->extractTextFromFile($filePath, $document->mime_type ?? '', $selectedChapters);
            }

            Log::info('Plagiarism section filter completed', [
                'check_id' => $check->id,
                'selected_sections' => $selectedChapters,
                'filtered_text_length' => mb_strlen($content),
            ]);

            if (empty($selectedChapters) && trim($content) === '' && trim((string) $document->content) !== '') {
                $content = (string) $document->content;
            }

            if (trim($content) === '') {
                $document->update(['status' => 'failed']);
                $check->update([
                    'status' => 'failed',
                    'error_message' => empty($selectedChapters)
                        ? 'Teks dokumen tidak dapat diekstrak. Pastikan file DOCX/PDF valid dan dukungan ekstraksi tersedia.'
                        : 'BAB yang dipilih tidak ditemukan dalam teks dokumen. Periksa format heading BAB lalu coba lagi.',
                ]);

                return;
            }

            $document->update([
                'content' => $content,
                'status' => 'processing',
            ]);

            $check = $plagiarismService->check(
                $document->fresh(),
                $check->sources_checked ?? [],
                $settings,
                $check,
            );

            if ($check->status === 'completed'
                && ! History::where('user_id', $check->user_id)
                    ->where('activity_type', 'plagiarism_check')
                    ->whereJsonContains('metadata->check_id', $check->id)
                    ->exists()) {
                $historyService->logPlagiarismCheck(
                    $check->document->user,
                    $check->id,
                    $check->document->title,
                    $check->total_similarity ?? 0,
                    $check->sources_checked ?? [],
                );
            }
        } catch (\Throwable $exception) {
            $check = PlagiarismCheck::find($this->checkId);

            if ($check) {
                $check->update([
                    'status' => 'failed',
                    'error_message' => $exception->getMessage(),
                ]);
            }

            Log::error('Plagiarism check job failed', [
                'check_id' => $this->checkId,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $check = PlagiarismCheck::find($this->checkId);

        if (! $check || $check->status === 'completed') {
            return;
        }

        $check->update([
            'status' => 'failed',
            'error_message' => $exception?->getMessage() ?: 'Pengecekan gagal diproses oleh queue worker.',
        ]);

        Log::error('Plagiarism check job failed', [
            'check_id' => $this->checkId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
