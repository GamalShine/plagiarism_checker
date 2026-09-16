<?php

namespace App\Services;

use App\Models\PlagiarismCheck;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Symfony\Component\HttpFoundation\Response;

class PlagiarismExportService
{
    public function __construct(
        private DocumentPageRenderer $documentPageRenderer,
    ) {}

    public function buildExportResponse(
        PlagiarismCheck $check,
        string $highlightedText,
        string $downloadName,
        bool $includeAllSources = false,
    ): Response {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        if (filter_var(env('PDF_LIGHTWEIGHT', false), FILTER_VALIDATE_BOOL) || ! function_exists('shell_exec')) {
            return $this->renderFallbackPdf($check, $downloadName, $includeAllSources);
        }

        $tempDir = storage_path('app/temp/exports/' . $check->id . '_' . time());
        File::ensureDirectoryExists($tempDir);

        $coverPath = $tempDir . DIRECTORY_SEPARATOR . 'cover.pdf';
        $reportPath = $tempDir . DIRECTORY_SEPARATOR . 'report.pdf';
        $mergedPath = $tempDir . DIRECTORY_SEPARATOR . 'merged.pdf';
        $highlightsManifest = $tempDir . DIRECTORY_SEPARATOR . 'highlights.json';

        $filePath = $check->document->file_path
            ? Storage::disk('public')->path($check->document->file_path)
            : '';
        $exportCachePath = $this->exportCachePath($check, $filePath, $highlightedText, $includeAllSources);

        if ($exportCachePath && is_file($exportCachePath) && filesize($exportCachePath) > 0) {
            return response()->download($exportCachePath, $downloadName, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        $sourcePdf = $this->documentPageRenderer->resolveSourcePdf($filePath, $check->document->id);
        $pageImages = [];

        $this->ensureHangulFont();

        $sourceIndexes = $check->sources->values()->mapWithKeys(
            fn ($source, $index) => [$source->id => $source->turnitin_index ?? ($index + 1)]
        );

        $exportHighlights = $check->highlights->map(fn ($highlight) => [
                'text' => $highlight->original_text,
                'color' => $highlight->color_code ?? $highlight->source?->color_code ?? '#ef4444',
                'source_index' => $sourceIndexes[$highlight->plagiarism_source_id] ?? 0,
            ])->values()->all();

        $highlightedSourceIds = $check->highlights
            ->pluck('plagiarism_source_id')
            ->unique()
            ->all();

        foreach ($check->sources as $source) {
            if (in_array($source->id, $highlightedSourceIds, true)) {
                continue;
            }

            foreach ([(string) $source->snippet, (string) $source->title] as $candidate) {
                $candidate = trim($candidate);
                if (mb_strlen($candidate) < 10) {
                    continue;
                }

                $exportHighlights[] = [
                    'text' => mb_substr($candidate, 0, 500),
                    'color' => $source->color_code ?? '#ef4444',
                    'source_index' => $sourceIndexes[$source->id] ?? 0,
                ];
                break;
            }
        }

        file_put_contents($highlightsManifest, json_encode(
            $exportHighlights,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ));

        $this->renderPartialPdf('plagiarism.export_cover', compact('check'), $coverPath);
        $this->renderPartialPdf(
            'plagiarism.export_report',
            compact('check', 'highlightedText', 'includeAllSources'),
            $reportPath
        );

        if ($sourcePdf && $this->mergePdfs(
            $mergedPath,
            $coverPath,
            $reportPath,
            $sourcePdf,
            $highlightsManifest,
            'trn:oid:::9817:193844' . str_pad((string) $check->document_id, 3, '0', STR_PAD_LEFT)
        )) {
            @unlink($coverPath);
            @unlink($reportPath);
            @unlink($highlightsManifest);

            if ($exportCachePath) {
                @copy($mergedPath, $exportCachePath);
                @unlink($mergedPath);
            }

            return response()->download($exportCachePath ?: $mergedPath, $downloadName, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(! $exportCachePath);
        }

        Log::warning('PDF merge unavailable, falling back to single PDF export', [
            'check_id' => $check->id,
            'source_pdf' => $sourcePdf,
            'page_images' => count($pageImages),
        ]);

        @unlink($coverPath);
        @unlink($reportPath);
        @unlink($highlightsManifest);

        return $this->renderReportPdf(
            $check,
            $highlightedText,
            $downloadName,
            $includeAllSources,
            $pageImages,
        );
    }

    private function renderReportPdf(
        PlagiarismCheck $check,
        string $highlightedText,
        string $downloadName,
        bool $includeAllSources = false,
        array $pageImages = [],
    ): Response {
        return Pdf::loadView('plagiarism.export_pdf', [
            'check' => $check,
            'highlightedText' => $highlightedText,
            'pageImages' => $pageImages,
            'includeAllSources' => $includeAllSources,
        ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'chroot' => base_path(),
            ])
            ->download($downloadName);
    }

    private function renderSummaryPdf(
        PlagiarismCheck $check,
        string $downloadName,
        bool $includeAllSources = false,
    ): Response {
        $check->loadMissing(['document', 'sources', 'highlights.source']);
        $highlightedText = $this->buildLightweightHighlightedText($check);

        return Pdf::loadView('plagiarism.export_report', [
            'check' => $check,
            'highlightedText' => $highlightedText,
            'includeAllSources' => $includeAllSources,
        ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => false,
                'isRemoteEnabled' => false,
                'chroot' => base_path(),
            ])
            ->download($downloadName);
    }

    private function buildLightweightHighlightedText(PlagiarismCheck $check): string
    {
        $content = trim((string) $check->document->content);
        if ($content === '') {
            return '';
        }

        $text = htmlspecialchars(mb_substr($content, 0, 120000), ENT_QUOTES, 'UTF-8');
        $highlights = $check->highlights
            ->filter(fn ($highlight) => mb_strlen(trim((string) $highlight->original_text)) >= 10)
            ->sortByDesc(fn ($highlight) => mb_strlen((string) $highlight->original_text))
            ->take(100);

        foreach ($highlights as $highlight) {
            $needle = htmlspecialchars(trim((string) $highlight->original_text), ENT_QUOTES, 'UTF-8');
            if ($needle === '') {
                continue;
            }

            $color = $highlight->color_code ?? $highlight->source?->color_code ?? '#facc15';
            $safeColor = preg_match('/^#?[0-9a-fA-F]{6,8}$/', (string) $color) ? '#' . strtoupper(substr(preg_replace('/[^0-9A-Fa-f]/', '', (string) $color), 0, 6)) : '#FACC15';
            $replacement = '<span style="display:inline-block; background:' . htmlspecialchars($safeColor, ENT_QUOTES, 'UTF-8') . '; border-bottom:2px solid ' . htmlspecialchars($safeColor, ENT_QUOTES, 'UTF-8') . '; padding:0 2px; border-radius:2px;">' . $needle . '</span>';
            $text = str_replace($needle, $replacement, $text);
        }

        return nl2br($text);
    }

    private function renderFallbackPdf(
        PlagiarismCheck $check,
        string $downloadName,
        bool $includeAllSources = false,
    ): Response {
        $filePath = $check->document->file_path
            ? Storage::disk('public')->path($check->document->file_path)
            : '';

        $sourcePdf = $this->documentPageRenderer->resolveSourcePdfWithoutShell(
            $filePath,
            $check->document->id,
            $check->highlights->all(),
        );

        if (! $sourcePdf) {
            return $this->renderSummaryPdf($check, $downloadName, $includeAllSources);
        }

        $tempDir = storage_path('app/temp/exports/no-python_' . $check->id . '_' . time());
        File::ensureDirectoryExists($tempDir);
        $coverPath = $tempDir . DIRECTORY_SEPARATOR . 'cover.pdf';
        $reportPath = $tempDir . DIRECTORY_SEPARATOR . 'report.pdf';
        $mergedPath = $tempDir . DIRECTORY_SEPARATOR . 'merged.pdf';

        try {
            $this->renderPartialPdf('plagiarism.export_cover', compact('check'), $coverPath);
            $this->renderPartialPdf('plagiarism.export_report', [
                'check' => $check,
                'highlightedText' => '',
                'includeAllSources' => $includeAllSources,
            ], $reportPath);

            if ($this->mergePdfFiles($mergedPath, [$coverPath, $sourcePdf, $reportPath])) {
                return response()->download($mergedPath, $downloadName, [
                    'Content-Type' => 'application/pdf',
                ])->deleteFileAfterSend(true);
            }
        } catch (\Throwable $e) {
            Log::warning('No-Python PDF export failed', [
                'check_id' => $check->id,
                'error' => $e->getMessage(),
            ]);
        } finally {
            @unlink($coverPath);
            @unlink($reportPath);
        }

        return $this->renderSummaryPdf($check, $downloadName, $includeAllSources);
    }

    private function mergePdfFiles(string $outputPath, array $pdfPaths): bool
    {
        $pdf = new Fpdi();
        $importedPage = false;

        foreach ($pdfPaths as $pdfPath) {
            if (! is_string($pdfPath) || ! is_file($pdfPath) || filesize($pdfPath) <= 0) {
                continue;
            }

            $pageCount = $pdf->setSourceFile($pdfPath);
            for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
                $template = $pdf->importPage($pageNumber);
                $size = $pdf->getTemplateSize($template);
                $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($template, 0, 0, $size['width'], $size['height']);
                $importedPage = true;
            }
        }

        if (! $importedPage) {
            return false;
        }

        $pdf->Output('F', $outputPath);

        return is_file($outputPath) && filesize($outputPath) > 0;
    }

    private function renderPartialPdf(string $view, array $data, string $outputPath): void
    {
        Pdf::loadView($view, $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'chroot' => base_path(),
            ])
            ->save($outputPath);
    }

    private function ensureHangulFont(): void
    {
        $fontDirectory = storage_path('app/fonts');
        $fontPath = $fontDirectory . DIRECTORY_SEPARATOR . 'malgun.ttf';

        if (is_file($fontPath)) {
            return;
        }

        File::ensureDirectoryExists($fontDirectory);
        $systemFont = 'C:\\Windows\\Fonts\\malgun.ttf';

        if (is_file($systemFont)) {
            @copy($systemFont, $fontPath);
        }
    }

    private function mergePdfs(
        string $outputPath,
        string $coverPath,
        string $reportPath,
        ?string $sourcePath = null,
        ?string $highlightsManifest = null,
        ?string $submissionId = null
    ): bool {
        if (! function_exists('shell_exec') || ! $sourcePath) {
            return false;
        }

        $node = $this->findNodeBinary();
        $script = base_path('scripts/merge_pdfs.mjs');

        if (!$node || !is_file($script)) {
            return false;
        }

        $maxPages = (int) env('PDF_PAGE_MAX', 200);
        $submissionId = $submissionId ?? '';

        $command = sprintf(
            '%s %s %s --cover %s --source %s --report %s --highlights %s --max-pages %d',
            escapeshellarg($node),
            escapeshellarg($script),
            escapeshellarg($outputPath),
            escapeshellarg($coverPath),
            escapeshellarg($sourcePath),
            escapeshellarg($reportPath),
            escapeshellarg($highlightsManifest ?? ''),
            $maxPages
        );

        $stderrPath = $outputPath . '.stderr';
        $command .= ' 2> ' . escapeshellarg($stderrPath);

        $output = shell_exec($command);
        @unlink($stderrPath);
        if (!is_string($output) || trim($output) === '') {
            return false;
        }

        $decoded = json_decode(trim($output), true);
        if (!is_array($decoded) || !empty($decoded['error'])) {
            Log::warning('PDF merge failed', [
                'output' => $output,
                'error' => $decoded['error'] ?? 'invalid response',
            ]);

            return false;
        }

        return is_file($outputPath) && filesize($outputPath) > 0;
    }

    private function findNodeBinary(): ?string
    {
        $candidates = array_filter([
            env('NODE_PATH'),
            'C:\\Program Files\\nodejs\\node.exe',
            'node',
        ]);

        foreach ($candidates as $candidate) {
            if ($candidate === 'node') {
                return $candidate;
            }

            if (is_string($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function exportCachePath(
        PlagiarismCheck $check,
        string $filePath,
        string $highlightedText,
        bool $includeAllSources,
    ): ?string {
        if (! filter_var(env('PDF_EXPORT_CACHE', true), FILTER_VALIDATE_BOOL)) {
            return null;
        }

        $sourceStamp = is_file($filePath)
            ? (string) (filemtime($filePath) ?: 0) . ':' . (string) (filesize($filePath) ?: 0)
            : 'missing';
        $cacheKey = sha1(implode('|', [
            $check->id,
            (string) $check->updated_at,
            $sourceStamp,
            $highlightedText,
            $includeAllSources ? 'all' : 'primary',
            (string) env('PDF_PAGE_MAX', 200),
            'source-snippet-fallback-v1',
        ]));
        $directory = storage_path('app/temp/exports/cache');
        File::ensureDirectoryExists($directory);

        return $directory . DIRECTORY_SEPARATOR . $cacheKey . '.pdf';
    }
}
