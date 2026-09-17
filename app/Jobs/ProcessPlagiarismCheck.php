<?php

namespace App\Jobs;

use App\Models\PlagiarismCheck;
use App\Models\History;
use App\Services\HistoryService;
use App\Services\DocumentPageRenderer;
use App\Services\PlagiarismExportService;
use App\Services\PlagiarismService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessPlagiarismCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;

    public function __construct(public int $checkId, public array $chapters = [])
    {
        $this->onQueue('plagiarism');
    }

    public function handle(
        PlagiarismService $plagiarismService,
        HistoryService $historyService,
        DocumentPageRenderer $documentPageRenderer,
        PlagiarismExportService $plagiarismExportService,
    ): void
    {
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

        $selectedChapters = $this->chapters ?: ($check->chapters ?? []);

        Log::info('Plagiarism section filter started', [
            'check_id' => $check->id,
            'selected_sections' => $selectedChapters,
            'selected_count' => count($selectedChapters),
        ]);

        $document = $check->document;
        $filePath = $document->file_path
            ? Storage::disk('public')->path($document->file_path)
            : '';

        $sourcePdf = $filePath ? $documentPageRenderer->resolveSourcePdf($filePath, $document->id) : null;
        if ($filePath && strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'docx' && ! $sourcePdf) {
            Log::warning('Source DOCX could not be converted to PDF during plagiarism processing', [
                'check_id' => $check->id,
                'document_id' => $document->id,
            ]);
        }

        $content = $filePath
            ? $plagiarismService->extractTextFromFile($filePath, $document->mime_type ?? '', $selectedChapters)
            : (string) $document->content;

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

        if ($check->status === 'completed') {
            $plagiarismExportService->buildHighlightedSourcePdf($check->fresh());
        }

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
    }
}
