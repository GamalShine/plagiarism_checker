<?php

namespace App\Jobs;

use App\Models\PlagiarismCheck;
use App\Services\PlagiarismExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GeneratePlagiarismExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 1;
    public int $backoff = 0;

    public function __construct(
        public int $checkId,
        public string $downloadName = 'plagiarism_report.pdf',
        public bool $includeAllSources = false,
    ) {
        $this->onQueue('plagiarism');
    }

    public function handle(PlagiarismExportService $plagiarismExportService): void
    {
        $check = PlagiarismCheck::with([
            'document',
            'sources' => fn ($query) => $query->orderByDesc('similarity_score'),
            'highlights.source',
        ])->find($this->checkId);

        if (! $check) {
            Log::warning('Queued export skipped because the plagiarism check no longer exists.', [
                'check_id' => $this->checkId,
            ]);

            return;
        }

        $sourceIndexMap = [];
        foreach ($check->sources as $index => $source) {
            $source->turnitin_index = $index + 1;
            $sourceIndexMap[$source->id] = $index + 1;
        }

        $isDocx = strtolower(pathinfo($check->document->file_path ?? $check->document->original_filename, PATHINFO_EXTENSION)) === 'docx';
        $highlightedText = ! $isDocx
            ? app(\App\Http\Controllers\PlagiarismController::class)->buildHighlightedText($check, $sourceIndexMap)
            : '';

        $relativePath = $plagiarismExportService->storeGeneratedExport(
            $check,
            $highlightedText,
            $this->downloadName,
            $this->includeAllSources,
        );

        Log::info('Queued PDF export generated successfully.', [
            'check_id' => $check->id,
            'relative_path' => $relativePath,
        ]);
    }
}
