<?php

namespace App\Services;

use App\Models\PlagiarismCheck;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
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

        $sourcePdf = $this->documentPageRenderer->resolveSourcePdfWithPhpWord($filePath, $check->document->id);
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

        if ($sourcePdf && $this->mergePdfFilesWithPhpHighlights(
            $mergedPath,
            $coverPath,
            $sourcePdf,
            $reportPath,
            $highlightsManifest,
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

        return $this->renderCoverAndReportPdf($check, $highlightedText, $downloadName, $includeAllSources);
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

        $sourcePdf = $this->documentPageRenderer->resolveSourcePdfWithPhpWord($filePath, $check->document->id);

        if (! $sourcePdf) {
            return $this->renderCoverAndReportPdf(
                $check,
                $this->buildLightweightHighlightedText($check),
                $downloadName,
                $includeAllSources,
            );
        }

        $fallbackSourceIndexes = $check->sources->values()->mapWithKeys(
            fn ($source, $index) => [$source->id => $index + 1]
        );
        $fallbackHighlights = $check->highlights->map(fn ($highlight) => [
            'text' => $highlight->original_text,
            'color' => $highlight->color_code ?? $highlight->source?->color_code ?? '#facc15',
            'source_index' => $fallbackSourceIndexes[$highlight->plagiarism_source_id] ?? 0,
        ])->values()->all();

        foreach ($check->sources as $index => $source) {
            if ($check->highlights->contains('plagiarism_source_id', $source->id)) {
                continue;
            }

            foreach ([(string) $source->snippet, (string) $source->title] as $candidate) {
                if (mb_strlen(trim($candidate)) < 10) {
                    continue;
                }
                $fallbackHighlights[] = [
                    'text' => mb_substr(trim($candidate), 0, 500),
                    'color' => $source->color_code ?? '#facc15',
                    'source_index' => $fallbackSourceIndexes[$source->id] ?? ($index + 1),
                ];
                break;
            }
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

            if ($this->mergePdfFilesWithPhpHighlights(
                $mergedPath,
                $coverPath,
                $sourcePdf,
                $reportPath,
                null,
                $fallbackHighlights,
            )) {
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

        return $this->renderCoverAndReportPdf(
            $check,
            $this->buildLightweightHighlightedText($check),
            $downloadName,
            $includeAllSources,
        );
    }

    private function renderCoverAndReportPdf(
        PlagiarismCheck $check,
        string $highlightedText,
        string $downloadName,
        bool $includeAllSources = false,
    ): Response {
        $tempDir = storage_path('app/temp/exports/report-only_' . $check->id . '_' . time());
        File::ensureDirectoryExists($tempDir);
        $coverPath = $tempDir . DIRECTORY_SEPARATOR . 'cover.pdf';
        $documentPath = $tempDir . DIRECTORY_SEPARATOR . 'document.pdf';
        $reportPath = $tempDir . DIRECTORY_SEPARATOR . 'report.pdf';
        $mergedPath = $tempDir . DIRECTORY_SEPARATOR . 'merged.pdf';

        try {
            $this->renderPartialPdf('plagiarism.export_cover', compact('check'), $coverPath);
            $this->renderPartialPdf('plagiarism.export_document', [
                'check' => $check,
                'highlightedText' => $highlightedText,
            ], $documentPath);
            $this->renderPartialPdf('plagiarism.export_report', [
                'check' => $check,
                'highlightedText' => '',
                'includeAllSources' => $includeAllSources,
            ], $reportPath);

            if ($this->mergePdfFiles($mergedPath, [$coverPath, $documentPath, $reportPath])) {
                return response()->download($mergedPath, $downloadName, [
                    'Content-Type' => 'application/pdf',
                ])->deleteFileAfterSend(true);
            }
        } finally {
            @unlink($coverPath);
            @unlink($documentPath);
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

    private function mergePdfFilesWithPhpHighlights(
        string $outputPath,
        string $coverPath,
        string $sourcePath,
        string $reportPath,
        ?string $highlightsManifest = null,
        array $inlineHighlights = [],
    ): bool {
        if (! is_file($sourcePath) || filesize($sourcePath) <= 0) {
            return false;
        }

        try {
            $manifestHighlights = $highlightsManifest && is_file($highlightsManifest)
                ? json_decode((string) file_get_contents($highlightsManifest), true)
                : [];
            $highlights = is_array($manifestHighlights) && $manifestHighlights !== []
                ? $manifestHighlights
                : $inlineHighlights;

            $parsedDocument = (new Parser())->parseFile($sourcePath);
            $parsedPages = $parsedDocument->getPages();
            $pdf = new Fpdi();

            $this->appendPdfPages($pdf, $coverPath);

            $sourcePageCount = $pdf->setSourceFile($sourcePath);
            for ($pageNumber = 1; $pageNumber <= $sourcePageCount; $pageNumber++) {
                $template = $pdf->importPage($pageNumber);
                $size = $pdf->getTemplateSize($template);
                $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($template, 0, 0, $size['width'], $size['height']);

                $parserPage = $parsedPages[$pageNumber - 1] ?? null;
                if (! $parserPage || ! is_array($highlights)) {
                    continue;
                }

                foreach ($highlights as $highlight) {
                    $boxes = $this->findPhpHighlightBoxes(
                        $parserPage,
                        (string) ($highlight['text'] ?? ''),
                        (float) $size['height'],
                    );
                    if ($boxes === []) {
                        continue;
                    }

                    [$red, $green, $blue] = $this->parsePhpHighlightColor($highlight['color'] ?? '#facc15');
                    $pdf->SetFillColor($red, $green, $blue);
                    $pdf->SetDrawColor($red, $green, $blue);
                    foreach ($boxes as $box) {
                        $pdf->Rect($box['x'], $box['y'], $box['width'], $box['height'], 'F');
                    }
                }
            }

            $this->appendPdfPages($pdf, $reportPath);
            $pdf->Output('F', $outputPath);

            return is_file($outputPath) && filesize($outputPath) > 0;
        } catch (\Throwable $e) {
            Log::warning('PHP-only PDF export failed', [
                'source' => $sourcePath,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function appendPdfPages(Fpdi $pdf, string $path): void
    {
        if (! is_file($path) || filesize($path) <= 0) {
            return;
        }

        $pageCount = $pdf->setSourceFile($path);
        for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
            $template = $pdf->importPage($pageNumber);
            $size = $pdf->getTemplateSize($template);
            $orientation = $size['width'] > $size['height'] ? 'L' : 'P';
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($template, 0, 0, $size['width'], $size['height']);
        }
    }

    private function findPhpHighlightBoxes(object $page, string $needle, float $pageHeight): array
    {
        $needle = $this->normalizePdfText($needle);
        if (mb_strlen($needle) < 4) {
            return [];
        }

        $items = [];
        foreach ($page->getDataTm() as $entry) {
            $matrix = $entry[0] ?? [];
            $text = $this->normalizePdfText((string) ($entry[1] ?? ''));
            if ($text === '' || ! isset($matrix[4], $matrix[5])) {
                continue;
            }

            $fontSize = isset($entry[3]) && is_numeric($entry[3]) ? (float) $entry[3] : 10.0;
            $items[] = [
                'text' => $text,
                'start' => 0,
                'end' => 0,
                'x' => (float) $matrix[4],
                'y' => (float) $matrix[5],
                'font_size' => max(5.0, $fontSize),
            ];
        }

        $joined = '';
        foreach ($items as &$item) {
            $item['start'] = mb_strlen($joined);
            $joined .= $item['text'];
            $item['end'] = mb_strlen($joined);
            $joined .= ' ';
        }
        unset($item);

        $matchStart = mb_stripos($joined, $needle);
        if ($matchStart === false) {
            return [];
        }

        $matchEnd = $matchStart + mb_strlen($needle);
        $boxes = [];
        foreach ($items as $item) {
            if ($item['end'] <= $matchStart || $item['start'] >= $matchEnd) {
                continue;
            }

            $fontSize = $item['font_size'];
            $width = max(4.0, mb_strlen($item['text']) * $fontSize * 0.5);
            $boxes[] = [
                'x' => max(0.0, $item['x'] - 1.0),
                'y' => max(0.0, $pageHeight - $item['y'] - $fontSize * 1.15),
                'width' => $width + 2.0,
                'height' => $fontSize * 1.25,
            ];
        }

        return $boxes;
    }

    private function normalizePdfText(string $text): string
    {
        return mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', $text)));
    }

    /** @return array{0:int,1:int,2:int} */
    private function parsePhpHighlightColor(string $value): array
    {
        if (preg_match('/^#?([0-9a-f]{6})$/i', trim($value), $match)) {
            return [
                hexdec(substr($match[1], 0, 2)),
                hexdec(substr($match[1], 2, 2)),
                hexdec(substr($match[1], 4, 2)),
            ];
        }

        return [250, 204, 21];
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
            'highlighted-document-fallback-v2',
        ]));
        $directory = storage_path('app/temp/exports/cache');
        File::ensureDirectoryExists($directory);

        return $directory . DIRECTORY_SEPARATOR . $cacheKey . '.pdf';
    }
}
