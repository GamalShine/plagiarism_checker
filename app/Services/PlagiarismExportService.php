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
    private const EXPORT_CACHE_VERSION = 'v4';

    public function __construct(
        private DocumentPageRenderer $documentPageRenderer,
    ) {}

    public function shouldQueueExport(): bool
    {
        $explicit = env('PDF_EXPORT_QUEUE');
        if ($explicit !== null) {
            return filter_var($explicit, FILTER_VALIDATE_BOOL);
        }

        return config('queue.default') !== 'sync';
    }

    public function getExportDiskPath(PlagiarismCheck $check): string
    {
        return 'exports/' . self::EXPORT_CACHE_VERSION . '/plagiarism_' . $check->id . '.pdf';
    }

    public function storeGeneratedExport(
        PlagiarismCheck $check,
        string $highlightedText,
        string $downloadName,
        bool $includeAllSources = false,
    ): string {
        $exportPath = $this->getExportDiskPath($check);
        $response = $this->buildExportResponse(
            $check,
            $highlightedText,
            $downloadName,
            $includeAllSources,
            deleteTemporaryFile: false,
        );

        $file = method_exists($response, 'getFile') ? $response->getFile() : null;
        if (! $file instanceof \SplFileInfo || ! $file->isFile()) {
            throw new \RuntimeException('Queued export response could not resolve a PDF file for check #' . $check->id);
        }

        $content = file_get_contents($file->getPathname());
        Storage::disk('public')->put($exportPath, $content !== false ? $content : '');

        return $exportPath;
    }

    public function buildExportResponse(
        PlagiarismCheck $check,
        string $highlightedText,
        string $downloadName,
        bool $includeAllSources = false,
        bool $deleteTemporaryFile = true,
    ): Response {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        return $this->renderNoPythonPdf(
            $check,
            $highlightedText,
            $downloadName,
            $includeAllSources,
        );

        $tempDir = storage_path('app/temp/exports/' . $check->id . '_' . time());
        File::ensureDirectoryExists($tempDir);

        $coverPath = $tempDir . DIRECTORY_SEPARATOR . 'cover.pdf';
        $reportPath = $tempDir . DIRECTORY_SEPARATOR . 'report.pdf';
        $mergedPath = $tempDir . DIRECTORY_SEPARATOR . 'merged.pdf';
        $sourceImagesManifest = $tempDir . DIRECTORY_SEPARATOR . 'source_images.json';
        $highlightsManifest = $tempDir . DIRECTORY_SEPARATOR . 'highlights.json';

        $sourcePalette = [
            '#EF4444', '#3B82F6', '#10B981', '#F59E0B', '#8B5CF6',
            '#14B8A6', '#EC4899', '#F97316', '#6366F1', '#06B6D4', '#64748B',
        ];

        $sourceIndexes = $check->sources->values()->mapWithKeys(
            function ($source, $index) use ($sourcePalette) {
                $source->turnitin_index = $index + 1;
                $source->color_code = $sourcePalette[$index % count($sourcePalette)];

                return [$source->id => $index + 1];
            }
        );

        foreach ($check->highlights as $highlight) {
            $sourceIndex = $sourceIndexes[$highlight->plagiarism_source_id] ?? 0;
            if ($sourceIndex > 0) {
                $highlight->color_code = $sourcePalette[($sourceIndex - 1) % count($sourcePalette)];
            }
        }

        $filePath = $check->document->file_path
            ? Storage::disk('public')->path($check->document->file_path)
            : '';

        // Python applies the annotations to the plain source PDF. Do not feed
        // it a PDF already highlighted by Word, or the colors stack and darken.
        $highlightedSourcePdf = $this->documentPageRenderer->resolveSourcePdf(
            $filePath,
            $check->document->id,
        );
        $sourcePdf = $highlightedSourcePdf;
        if (! $sourcePdf && $check->highlights->isEmpty()) {
            $sourcePdf = $this->documentPageRenderer->resolveSourcePdf($filePath, $check->document->id);
        }
        $pageImages = $sourcePdf ? [] : $this->documentPageRenderer->renderPages($filePath, $check->document->id);

        $this->ensureHangulFont();

        file_put_contents($highlightsManifest, json_encode(
            $check->highlights->map(fn ($highlight) => [
                'text' => $highlight->original_text,
                'color' => $highlight->color_code ?? $highlight->source?->color_code ?? 'transparent',
                'source_index' => $sourceIndexes[$highlight->plagiarism_source_id] ?? 0,
            ])->values()->all(),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ));

        $this->renderPartialPdf('plagiarism.export_cover', compact('check'), $coverPath);
        $this->renderPartialPdf(
            'plagiarism.export_report',
            compact('check', 'highlightedText', 'includeAllSources'),
            $reportPath
        );

        if ($pageImages !== []) {
            file_put_contents($sourceImagesManifest, json_encode([
                'pages' => array_values($pageImages),
            ]));
        }

        $merged = ($pageImages !== [] || $sourcePdf) && $this->mergePdfs(
            $mergedPath,
            $coverPath,
            $reportPath,
            $pageImages !== [] ? $sourceImagesManifest : null,
            $sourcePdf,
            $highlightsManifest,
            'trn:oid:::9817:193844' . str_pad((string) $check->document_id, 3, '0', STR_PAD_LEFT)
        );

        if ($merged) {
            @unlink($coverPath);
            @unlink($reportPath);
            if (is_file($sourceImagesManifest)) {
                @unlink($sourceImagesManifest);
            }
            @unlink($highlightsManifest);

            return response()->download($mergedPath, $downloadName, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend($deleteTemporaryFile);
        }

        Log::warning('PDF merge unavailable, falling back to single PDF export', [
            'check_id' => $check->id,
            'source_pdf' => $sourcePdf,
            'page_images' => count($pageImages),
        ]);

        @unlink($coverPath);
        @unlink($reportPath);
        if (is_file($sourceImagesManifest)) {
            @unlink($sourceImagesManifest);
        }
        @unlink($highlightsManifest);

        return $this->renderReportPdf(
            $check,
            $highlightedText,
            $downloadName,
            $includeAllSources,
            $pageImages,
        );
    }

    public function buildHighlightedSourcePdf(PlagiarismCheck $check): ?string
    {
        $check->loadMissing(['document', 'highlights.source']);

        if (! $check->document || ! $check->document->file_path) {
            return null;
        }

        $filePath = Storage::disk('public')->path($check->document->file_path);

        if (! is_file($filePath)) {
            return null;
        }

        $highlightedPdf = $this->documentPageRenderer->resolveSourcePdfWithoutShell(
            $filePath,
            $check->document->id,
            $check->highlights->all(),
        );

        if ($highlightedPdf && is_file($highlightedPdf) && filesize($highlightedPdf) > 0) {
            return $highlightedPdf;
        }

        if ($check->highlights->isNotEmpty()) {
            return null;
        }

        $fallbackPdf = $this->documentPageRenderer->resolveSourcePdf($filePath, $check->document->id);

        return $fallbackPdf && is_file($fallbackPdf) && filesize($fallbackPdf) > 0 ? $fallbackPdf : null;
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
                'defaultFont' => 'Arial',
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
        $sourceIndexMap = [];
        foreach ($check->sources as $index => $source) {
            $sourceIndexMap[$source->id] = (int) ($source->turnitin_index ?? ($index + 1));
        }
        $highlightedText = $this->buildFallbackHighlightedText($check, $sourceIndexMap);

        return Pdf::loadView('plagiarism.export_report', [
            'check' => $check,
            'highlightedText' => $highlightedText,
            'includeAllSources' => $includeAllSources,
        ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => false,
                'isRemoteEnabled' => false,
                'defaultFont' => 'Arial',
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

            $color = 'transparent';
            $replacement = '<mark style="background-color: ' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . ';">' . $needle . '</mark>';
            $text = str_replace($needle, $replacement, $text);
        }

        return nl2br($text);
    }

    public function shouldUseLightweightPdfExport(): bool
    {
        return filter_var(env('PDF_LIGHTWEIGHT', false), FILTER_VALIDATE_BOOL);
    }

    private function shouldImportSourcePdfForExport(?string $sourcePdfPath, ?bool $lightweightMode = null): bool
    {
        if (! is_string($sourcePdfPath) || trim($sourcePdfPath) === '') {
            return false;
        }

        if (! is_file($sourcePdfPath) || filesize($sourcePdfPath) <= 0) {
            return false;
        }

        $effectiveMode = $lightweightMode ?? $this->shouldUseLightweightPdfExport();

        return ! $effectiveMode;
    }

    private function renderNoPythonPdf(
        PlagiarismCheck $check,
        string $highlightedText,
        string $downloadName,
        bool $includeAllSources = false,
    ): Response {
        $storedPath = (string) ($check->document->file_path ?? '');
        $filePath = $storedPath !== '' ? Storage::disk('public')->path($storedPath) : '';
        $extension = strtolower(pathinfo($storedPath ?: (string) $check->document->original_filename, PATHINFO_EXTENSION));
        $isSourcePdf = ($extension === 'pdf' || strtolower((string) $check->document->mime_type) === 'application/pdf')
            && is_file($filePath)
            && filesize($filePath) > 0;
        $sourcePdfPath = $isSourcePdf ? $filePath : null;

        if ($sourcePdfPath === null) {
            Log::warning('PDF export source file is unavailable; exporting extracted text if present', [
                'check_id' => $check->id,
                'document_id' => $check->document->id,
                'stored_path' => $storedPath,
                'resolved_path' => $filePath,
                'exists' => $filePath !== '' && is_file($filePath),
                'mime_type' => $check->document->mime_type,
                'content_length' => mb_strlen((string) $check->document->content),
                'highlights_count' => $check->highlights->count(),
            ]);
        }

        $sourceIndexMap = [];
        foreach ($check->sources as $index => $source) {
            $sourceIndexMap[$source->id] = (int) ($source->turnitin_index ?? ($index + 1));
        }

        $importOriginalSourcePdf = $this->shouldImportSourcePdfForExport($sourcePdfPath);

        if (! $importOriginalSourcePdf && $sourcePdfPath !== null) {
            Log::info('Shared-hosting lightweight export mode enabled; skipping raw-source PDF merge to keep document/highlight layout stable.', [
                'check_id' => $check->id,
                'source_pdf' => $sourcePdfPath,
                'pdf_lightweight' => $this->shouldUseLightweightPdfExport(),
            ]);
        }

        if ($extension === 'docx') {
            $documentHtml = $this->documentPageRenderer->renderDocxHtml($filePath, $check->document->id);
            if ($documentHtml !== null) {
                $highlightedText = $this->highlightDocumentHtml(
                    $documentHtml,
                    $check->highlights->all(),
                    $sourceIndexMap,
                );
            }
        }

        if (trim($highlightedText) === '') {
            $highlightedText = $this->buildFallbackHighlightedText($check, $sourceIndexMap);
        }

        $highlightedText = $this->justifyTwelvePointParagraphs($highlightedText);
        $highlightedText = $this->formatHeadingHierarchy($highlightedText);

        // PhpWord places named-page selection on the body as inline styles
        // (`style="page: page1"`), not only inside the extracted stylesheet.
        // Remove it from the actual HTML before Dompdf paginates; otherwise
        // its named page can bypass export_document's default @page margins.
        $highlightedText = preg_replace('/\bpage\s*:\s*page\d+\s*;?/i', '', $highlightedText) ?? $highlightedText;

        $documentStyles = '';
        if (preg_match_all('/<style\b[^>]*>(.*?)<\/style>/is', $highlightedText, $styleMatches)) {
            $documentStyles = implode("\n", $styleMatches[1]);
            $highlightedText = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $highlightedText) ?? $highlightedText;

            // Ignore imported page rules too; the export template owns the
            // final page geometry and sets the intended asymmetric margins.
            $documentStyles = preg_replace('/@page\b[^{}]*\{[^}]*\}/is', '', $documentStyles) ?? $documentStyles;
        }

        $tempDir = storage_path('app/temp/exports/no-python_' . $check->id . '_' . time());
        File::ensureDirectoryExists($tempDir);
        $coverPath = $tempDir . DIRECTORY_SEPARATOR . 'cover.pdf';
        $documentPath = $tempDir . DIRECTORY_SEPARATOR . 'document.pdf';
        $reportPath = $tempDir . DIRECTORY_SEPARATOR . 'report.pdf';
        $mergedPath = $tempDir . DIRECTORY_SEPARATOR . 'merged.pdf';

        $sourceImportFailed = false;
        $sourcePagesMerged = false;
        $appliedSourceHighlights = null;
        try {
            $this->ensureArialFont();

            $this->renderPartialPdf('plagiarism.export_cover', compact('check'), $coverPath);
            $pdfParts = [$coverPath];

            if ($importOriginalSourcePdf) {
                // Keep the uploaded PDF pages and apply PHP-side visual marks
                // during the FPDI merge instead of reducing the document to text.
                $pdfParts[] = $sourcePdfPath;
            } elseif (trim($highlightedText) !== '') {
                $this->renderPartialPdf('plagiarism.export_document', [
                    'documentHtml' => $highlightedText,
                    'documentStyles' => $documentStyles,
                ], $documentPath);
                $pdfParts[] = $documentPath;
            }

            $this->renderPartialPdf('plagiarism.export_report', [
                'check' => $check,
                'highlightedText' => '',
                'includeAllSources' => $includeAllSources,
            ], $reportPath);
            $pdfParts[] = $reportPath;

            $mergeSucceeded = $this->mergePdfFiles(
                $mergedPath,
                $pdfParts,
                $importOriginalSourcePdf ? $sourcePdfPath : null,
                $importOriginalSourcePdf ? $check->highlights->all() : [],
                $sourceIndexMap,
                $appliedSourceHighlights,
            );

            if ($mergeSucceeded) {
                $sourcePagesMerged = $importOriginalSourcePdf;
                if ($importOriginalSourcePdf
                    && $check->highlights->isNotEmpty()
                    && ($appliedSourceHighlights ?? 0) < $check->highlights->count()) {
                    $sourceImportFailed = true;
                    Log::warning('Not all source PDF highlights matched; switching to highlighted text fallback', [
                        'check_id' => $check->id,
                        'source_pdf' => $sourcePdfPath,
                        'applied_highlights' => $appliedSourceHighlights,
                        'highlights_count' => $check->highlights->count(),
                    ]);
                    @unlink($mergedPath);
                } else {
                    return response()->download($mergedPath, $downloadName, [
                        'Content-Type' => 'application/pdf',
                    ])->deleteFileAfterSend(true);
                }
            } elseif ($importOriginalSourcePdf) {
                $sourceImportFailed = true;
                Log::warning('FPDI returned no merged PDF; switching to highlighted text fallback', [
                    'check_id' => $check->id,
                    'source_pdf' => $sourcePdfPath,
                    'parts' => count($pdfParts),
                ]);
            }
        } catch (\Throwable $e) {
            $sourceImportFailed = $importOriginalSourcePdf;
            Log::warning('No-Python PDF export failed', [
                'check_id' => $check->id,
                'source_pdf' => $sourcePdfPath,
                'source_import_failed' => $sourceImportFailed,
                'error' => $e->getMessage(),
            ]);
        } finally {
            @unlink($coverPath);
            @unlink($documentPath);
            @unlink($reportPath);
        }

        if ($sourceImportFailed || ! $importOriginalSourcePdf) {
            $fallbackDir = storage_path('app/temp/exports/php-fallback_' . $check->id . '_' . time());
            File::ensureDirectoryExists($fallbackDir);
            $fallbackCoverPath = $fallbackDir . DIRECTORY_SEPARATOR . 'cover.pdf';
            $fallbackDocumentPath = $fallbackDir . DIRECTORY_SEPARATOR . 'document.pdf';
            $fallbackReportPath = $fallbackDir . DIRECTORY_SEPARATOR . 'report.pdf';
            $fallbackMergedPath = $fallbackDir . DIRECTORY_SEPARATOR . 'merged.pdf';

            try {
                $this->renderPartialPdf('plagiarism.export_cover', compact('check'), $fallbackCoverPath);
                $fallbackParts = [$fallbackCoverPath];
                if ($sourcePagesMerged && $importOriginalSourcePdf && $sourcePdfPath !== null) {
                    // Retain successfully imported original pages when falling
                    // back only because their text could not be highlighted.
                    $fallbackParts[] = $sourcePdfPath;
                }
                if (trim($highlightedText) !== '') {
                    $this->renderPartialPdf('plagiarism.export_document', [
                        'documentHtml' => $highlightedText,
                        'documentStyles' => $documentStyles,
                    ], $fallbackDocumentPath);
                    $fallbackParts[] = $fallbackDocumentPath;
                }
                $this->renderPartialPdf('plagiarism.export_report', [
                    'check' => $check,
                    'highlightedText' => '',
                    'includeAllSources' => $includeAllSources,
                ], $fallbackReportPath);
                $fallbackParts[] = $fallbackReportPath;

                if ($this->mergePdfFiles($fallbackMergedPath, $fallbackParts)) {
                    return response()->download($fallbackMergedPath, $downloadName, [
                        'Content-Type' => 'application/pdf',
                    ])->deleteFileAfterSend($deleteTemporaryFile);
                }
            } catch (\Throwable $e) {
                Log::error('PHP text-document export fallback failed', [
                    'check_id' => $check->id,
                    'content_length' => mb_strlen((string) $check->document->content),
                    'highlights_count' => $check->highlights->count(),
                    'error' => $e->getMessage(),
                ]);
            } finally {
                @unlink($fallbackCoverPath);
                @unlink($fallbackDocumentPath);
                @unlink($fallbackReportPath);
            }
        }

        Log::error('PDF export could not include document pages or extracted text', [
            'check_id' => $check->id,
            'document_id' => $check->document->id,
            'stored_path' => $storedPath,
            'resolved_path' => $filePath,
            'content_length' => mb_strlen((string) $check->document->content),
            'highlights_count' => $check->highlights->count(),
        ]);

        if (trim((string) $check->document->content) === '' && ! $sourcePagesMerged) {
            abort(422, 'Dokumen asli tidak dapat dimasukkan ke PDF dan teks dokumen tidak tersedia untuk fallback. Periksa file sumber dan storage hosting.');
        }

        abort(500, 'Export PDF gagal digabungkan. Periksa log Laravel untuk detail proses export.');
    }

    private function buildFallbackHighlightedText(PlagiarismCheck $check, array $sourceIndexMap): string
    {
        $content = str_replace(["\r\n", "\r"], "\n", (string) ($check->document->content ?? ''));
        if (trim($content) === '') {
            return '';
        }

        $highlights = [];
        foreach ($check->highlights as $highlight) {
            $needle = trim((string) $highlight->original_text);
            if (mb_strlen($needle) < 4) {
                continue;
            }

            $start = (int) $highlight->start_position;
            $end = (int) $highlight->end_position;
            if ($end <= $start || mb_substr($content, $start, $end - $start) !== $needle) {
                $foundAt = mb_stripos($content, $needle);
                if ($foundAt === false) {
                    continue;
                }
                $start = $foundAt;
                $end = $start + mb_strlen($needle);
            }

            $highlights[] = [
                'start' => $start,
                'end' => $end,
                'source_id' => $highlight->plagiarism_source_id,
                'color' => (string) ($highlight->color_code ?? $highlight->source?->color_code ?? '#FDE68A'),
                'text' => $needle,
            ];
        }

        usort($highlights, fn (array $left, array $right): int => $left['start'] <=> $right['start'] ?: $right['end'] <=> $left['end']);
        $html = '';
        $cursor = 0;
        foreach ($highlights as $highlight) {
            if ($highlight['start'] < $cursor) {
                continue;
            }

            $html .= htmlspecialchars(mb_substr($content, $cursor, $highlight['start'] - $cursor), ENT_QUOTES, 'UTF-8');
            $sourceIndex = $sourceIndexMap[$highlight['source_id']] ?? '*';
            $color = preg_match('/^#[0-9a-f]{6}$/i', $highlight['color']) === 1 ? $highlight['color'] : '#FDE68A';
            $safeText = htmlspecialchars(mb_substr($content, $highlight['start'], $highlight['end'] - $highlight['start']), ENT_QUOTES, 'UTF-8');
            $html .= '<mark class="t-highlight" style="background-color: ' . $color . '; border-bottom: 2px solid ' . $color . ';">'
                . '<sup class="t-badge" style="background-color: ' . $color . ';">' . htmlspecialchars((string) $sourceIndex, ENT_QUOTES, 'UTF-8') . '</sup>'
                . $safeText . '</mark>';
            $cursor = $highlight['end'];
        }

        $html .= htmlspecialchars(mb_substr($content, $cursor), ENT_QUOTES, 'UTF-8');

        return nl2br($html);
    }

    private function justifyTwelvePointParagraphs(string $html): string
    {
        if ($html === '' || ! class_exists(\DOMDocument::class)) {
            return $html;
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        @$dom->loadHTML('<?xml encoding="UTF-8"><div id="justify-root">' . $html . '</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
        $root = $dom->getElementById('justify-root');
        if (! $root) {
            return $html;
        }

        $xpath = new \DOMXPath($dom);
        foreach ($xpath->query('.//p', $root) ?: [] as $paragraph) {
            $elements = [$paragraph];
            foreach ($paragraph->getElementsByTagName('*') as $child) {
                $elements[] = $child;
            }

            $hasTwelvePointText = false;
            foreach ($elements as $element) {
                if (preg_match('/(?:^|;)\s*font-size\s*:\s*12(?:\.0+)?pt\b/i', $element->getAttribute('style'))) {
                    $hasTwelvePointText = true;
                    break;
                }
            }

            if (! $hasTwelvePointText) {
                continue;
            }

            $style = (string) $paragraph->getAttribute('style');
            $style = preg_replace('/(?:^|;)\s*text-align\s*:[^;]*/i', '', $style) ?? $style;
            $style = trim($style, " ;\t\n\r\0\x0B");
            $paragraph->setAttribute('style', ($style !== '' ? $style . '; ' : '') . 'text-align: justify !important;');
        }

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        return $result !== '' ? $result : $html;
    }

    private function formatHeadingHierarchy(string $html): string
    {
        if ($html === '' || ! class_exists(\DOMDocument::class)) {
            return $html;
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        @$dom->loadHTML('<?xml encoding="UTF-8"><div id="heading-root">' . $html . '</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
        $root = $dom->getElementById('heading-root');
        if (! $root) {
            return $html;
        }

        $xpath = new \DOMXPath($dom);
        $counters = [2 => 0, 3 => 0, 4 => 0];
        $currentBodyLevel = 0;
        $insideContents = false;

        foreach ($xpath->query('.//p', $root) ?: [] as $paragraph) {
            $level = (int) $paragraph->getAttribute('data-docx-heading-level');
            if ($level >= 1 && $level <= 4) {
                $headingText = mb_strtolower(trim($paragraph->textContent));
                $currentBodyLevel = $level;
                if ($level === 1) {
                    $counters = [2 => 0, 3 => 0, 4 => 0];
                    if (preg_match('/^daftar isi\b/u', $headingText)) {
                        $insideContents = true;
                    } elseif (preg_match('/^bab\s+i\b/u', $headingText)) {
                        $insideContents = false;
                    }
                } elseif ($level === 2) {
                    $counters[2]++;
                    $counters[3] = 0;
                    $counters[4] = 0;
                } elseif ($level === 3) {
                    $counters[3]++;
                    $counters[4] = 0;
                } else {
                    $counters[4]++;
                }

                $classNames = preg_split('/\s+/', trim($paragraph->getAttribute('class'))) ?: [];
                $classNames[] = 'doc-heading';
                $classNames[] = 'doc-heading-' . $level;
                $paragraph->setAttribute('class', implode(' ', array_unique(array_filter($classNames))));

                $paragraphStyle = (string) $paragraph->getAttribute('style');
                $paragraphStyle = $this->replaceInlineStyleProperty($paragraphStyle, 'text-align', $level === 1 ? 'center' : 'left');
                $paragraphStyle = $this->replaceInlineStyleProperty($paragraphStyle, 'font-weight', 'bold');
                if ($level === 1) {
                    // Content area is 0.2in right of the physical page center
                    // because the left and right page margins are asymmetric.
                    $paragraphStyle = $this->replaceInlineStyleProperty($paragraphStyle, 'position', 'relative');
                    $paragraphStyle = $this->replaceInlineStyleProperty($paragraphStyle, 'left', '-0.2in');
                }
                $leftIndent = match ($level) {
                    3 => '0.5in',
                    4 => '1in',
                    default => '0',
                };
                $paragraphStyle = $this->replaceInlineStyleProperty($paragraphStyle, 'margin-left', $leftIndent);
                $paragraphStyle = $this->replaceInlineStyleProperty($paragraphStyle, 'text-indent', '0');
                $paragraph->setAttribute('style', $paragraphStyle);

                foreach ($paragraph->getElementsByTagName('*') as $child) {
                    $child->setAttribute(
                        'style',
                        $this->replaceInlineStyleProperty((string) $child->getAttribute('style'), 'font-weight', 'bold'),
                    );
                }

                if ($level >= 2) {
                    $label = match ($level) {
                        2 => $this->alphabeticHeadingLabel($counters[2], true) . '.',
                        3 => $counters[3] . '.',
                        4 => $this->alphabeticHeadingLabel($counters[4], false) . ')',
                    };
                    $this->removeExistingHeadingNumber($paragraph, $level, $xpath);
                    $number = $dom->createElement('span');
                    $number->setAttribute('class', 'doc-heading-number');
                    $numberWeight = $level === 4 ? 'normal' : 'bold';
                    $number->setAttribute('style', 'font-weight: ' . $numberWeight . ' !important; display: inline-block; min-width: 0.6in; white-space: nowrap;');
                    $number->appendChild($dom->createTextNode($label . '  '));
                    $paragraph->insertBefore($number, $paragraph->firstChild);
                }

                continue;
            }

            if ($currentBodyLevel === 0
                || $insideContents
                || $paragraph->hasAttribute('data-docx-list-number')
                || $paragraph->parentNode instanceof \DOMElement && strtolower($paragraph->parentNode->tagName) === 'table'
                || $xpath->query('ancestor::table', $paragraph)?->length > 0
                || preg_match('/^toc(?:\b|\d)/i', $paragraph->getAttribute('data-docx-style'))
                || ! $this->paragraphContainsTwelvePointText($paragraph)) {
                continue;
            }

            $bodyIndent = match ($currentBodyLevel) {
                3 => '0.5in',
                4 => '1in',
                default => '0',
            };
            $paragraphStyle = (string) $paragraph->getAttribute('style');
            $paragraphStyle = $this->replaceInlineStyleProperty($paragraphStyle, 'margin-left', $bodyIndent);
            $paragraphStyle = $this->replaceInlineStyleProperty($paragraphStyle, 'text-indent', '0.5in');
            $paragraph->setAttribute('style', $paragraphStyle);
        }

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        return $result !== '' ? $result : $html;
    }

    private function paragraphContainsTwelvePointText(\DOMElement $paragraph): bool
    {
        $elements = [$paragraph];
        foreach ($paragraph->getElementsByTagName('*') as $child) {
            $elements[] = $child;
        }

        foreach ($elements as $element) {
            if (preg_match('/(?:^|;)\s*font-size\s*:\s*12(?:\.0+)?pt\b/i', $element->getAttribute('style'))) {
                return true;
            }
        }

        return false;
    }

    private function replaceInlineStyleProperty(string $style, string $property, string $value): string
    {
        $pattern = '/(?:^|;)\s*' . preg_quote($property, '/') . '\s*:[^;]*/i';
        $style = preg_replace($pattern, '', $style) ?? $style;
        $style = trim($style, " ;\t\n\r\0\x0B");

        return ($style !== '' ? $style . '; ' : '') . $property . ': ' . $value . ' !important;';
    }

    private function alphabeticHeadingLabel(int $number, bool $uppercase): string
    {
        $label = '';
        for ($value = max(1, $number); $value > 0; $value = intdiv($value - 1, 26)) {
            $label = chr(($uppercase ? 65 : 97) + (($value - 1) % 26)) . $label;
        }

        return $label;
    }

    private function removeExistingHeadingNumber(\DOMElement $paragraph, int $level, \DOMXPath $xpath): void
    {
        $text = $paragraph->textContent;
        $pattern = match ($level) {
            2 => '/^\s*[A-Z]\.\s*/u',
            3 => '/^\s*\d+(?:\.\d+)*[.)]\s*/u',
            4 => '/^\s*[a-z]\)\s*/iu',
            default => null,
        };

        if ($pattern === null || ! preg_match($pattern, $text, $matches)) {
            return;
        }

        $remaining = mb_strlen($matches[0]);
        foreach ($xpath->query('.//text()', $paragraph) ?: [] as $textNode) {
            if ($remaining <= 0) {
                break;
            }

            $textLength = mb_strlen($textNode->nodeValue ?? '');
            if ($textLength <= $remaining) {
                $textNode->nodeValue = '';
                $remaining -= $textLength;
            } else {
                $textNode->nodeValue = mb_substr((string) $textNode->nodeValue, $remaining);
                $remaining = 0;
            }
        }
    }

    private function highlightDocumentHtml(string $html, array $highlights, array $sourceIndexMap): string
    {
        if ($html === '' || $highlights === [] || ! class_exists(\DOMDocument::class)) {
            return $html;
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        @$dom->loadHTML('<?xml encoding="UTF-8"><div id="document-root">' . $html . '</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
        $root = $dom->getElementById('document-root');
        if (! $root) {
            return $html;
        }

        $items = collect($highlights)
            ->filter(fn ($highlight) => mb_strlen(trim((string) ($highlight->original_text ?? ''))) >= 4)
            ->sortByDesc(fn ($highlight) => mb_strlen((string) $highlight->original_text));

        foreach ($root->getElementsByTagName('*') as $element) {
            if (in_array(strtolower($element->nodeName), ['script', 'style', 'mark'], true)) {
                continue;
            }

            foreach (iterator_to_array($element->childNodes) as $child) {
                if ($child->nodeType !== XML_TEXT_NODE || trim($child->nodeValue) === '') {
                    continue;
                }

                $this->highlightTextNode($dom, $element, $child, $items, $sourceIndexMap);
            }
        }

        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        return $result !== '' ? $result : $html;
    }

    private function highlightTextNode(\DOMDocument $dom, \DOMElement $parent, \DOMText $textNode, $items, array $sourceIndexMap): void
    {
        $sourcePalette = [
            '#DE60E5',
            '#D763FF',
            '#25B3B3',
            '#0A9D02',
            '#A47108',
            '#7A2F08',
            '#0A476F',
            '#9C449B',
            '#808080',
        ];
        $text = $textNode->nodeValue;
        $cursor = 0;
        $matches = [];

        foreach ($items as $highlight) {
            $cleanText = trim(preg_replace('/\s+/u', ' ', (string) $highlight->original_text) ?? '');
            if ($cleanText === '') {
                continue;
            }

            $words = array_values(array_filter(explode(' ', $cleanText), fn ($word) => mb_strlen($word) > 2));
            $phrases = [$cleanText];
            if (count($words) >= 4) {
                $phrases[] = implode(' ', array_slice($words, 0, 6));
                if (count($words) >= 10) {
                    $phrases[] = implode(' ', array_slice($words, 4, 6));
                }
            }

            foreach (array_unique($phrases) as $needle) {
                foreach ($this->normalizedTextMatches($text, $needle) as [$start, $end]) {
                    $matches[] = [
                        'start' => $start,
                        'end' => $end,
                        'highlight' => $highlight,
                    ];
                }
            }
        }

        if ($matches === []) {
            return;
        }

        usort($matches, fn ($left, $right) => $left['start'] <=> $right['start'] ?: $right['end'] <=> $left['end']);
        $selected = [];
        foreach ($matches as $match) {
            if ($match['start'] < $cursor) {
                continue;
            }
            $selected[] = $match;
            $cursor = $match['end'];
        }

        if ($selected === []) {
            return;
        }

        $fragment = $dom->createDocumentFragment();
        $cursor = 0;
        foreach ($selected as $match) {
            if ($match['start'] > $cursor) {
                $fragment->appendChild($dom->createTextNode(mb_substr($text, $cursor, $match['start'] - $cursor)));
            }

            $highlight = $match['highlight'];
            $sourceId = $highlight->plagiarism_source_id;
            $sourceIndex = $sourceIndexMap[$sourceId] ?? '*';
            $paletteIndex = is_numeric($sourceIndex) ? max(1, (int) $sourceIndex) - 1 : 0;
            $color = $sourcePalette[$paletteIndex % count($sourcePalette)];
            $mark = $dom->createElement('mark');
            $mark->setAttribute('class', 't-highlight');
            $mark->setAttribute('data-source-id', (string) $sourceId);
            $mark->setAttribute('data-source-index', (string) $sourceIndex);
            $mark->setAttribute('data-source-color', $color);
            $mark->setAttribute('style', 'background-color: ' . $color . '33; border-bottom: 2px solid ' . $color . ';');
            $badge = $dom->createElement('span');
            $badge->setAttribute('class', 't-badge t-badge-main');
            $badge->setAttribute('style', 'background-color: ' . $color . '; margin-left: 1em;');
            $badge->appendChild($dom->createTextNode((string) $sourceIndex));
            $mark->appendChild($badge);
            $mark->appendChild($dom->createTextNode(mb_substr($text, $match['start'], $match['end'] - $match['start'])));
            $fragment->appendChild($mark);
            $cursor = $match['end'];
        }

        if ($cursor < mb_strlen($text)) {
            $fragment->appendChild($dom->createTextNode(mb_substr($text, $cursor)));
        }

        $parent->replaceChild($fragment, $textNode);
    }

    /** @return array<int, array{0: int, 1: int}> */
    private function normalizedTextMatches(string $text, string $needle): array
    {
        $normalizedText = '';
        $positions = [];
        $length = mb_strlen($text);
        $inWhitespace = false;

        for ($index = 0; $index < $length; $index++) {
            $character = mb_substr($text, $index, 1);
            if (preg_match('/\s/u', $character) === 1) {
                if ($normalizedText !== '' && ! $inWhitespace) {
                    $positions[] = $index;
                    $normalizedText .= ' ';
                }
                $inWhitespace = true;
                continue;
            }

            $positions[] = $index;
            $normalizedText .= $character;
            $inWhitespace = false;
        }

        $normalizedNeedle = trim(preg_replace('/\s+/u', ' ', $needle) ?? '');
        if ($normalizedNeedle === '' || $normalizedText === '') {
            return [];
        }

        $matches = [];
        $offset = 0;
        $needleLength = mb_strlen($normalizedNeedle);
        while (($position = mb_stripos($normalizedText, $normalizedNeedle, $offset)) !== false) {
            $endPosition = $position + $needleLength - 1;
            if (isset($positions[$position], $positions[$endPosition])) {
                $matches[] = [$positions[$position], $positions[$endPosition] + 1];
            }
            $offset = $position + max(1, $needleLength);
        }

        return $matches;
    }

    private function mergePdfFiles(
        string $outputPath,
        array $pdfPaths,
        ?string $highlightedSourcePath = null,
        array $highlights = [],
        array $sourceIndexMap = [],
        ?int &$appliedSourceHighlights = null,
    ): bool {
        $pdf = new Fpdi();
        $importedPage = false;
        $sourceTextRuns = [];
        $appliedSourceHighlights = 0;

        if ($highlightedSourcePath !== null
            && $highlights !== []
            && (filesize($highlightedSourcePath) ?: 0) <= 8 * 1024 * 1024) {
            try {
                $parserConfig = new \Smalot\PdfParser\Config();
                $parserConfig->setDataTmFontInfoHasToBeIncluded(true);
                $parser = new \Smalot\PdfParser\Parser([], $parserConfig);
                $sourceDocument = $parser->parseFile($highlightedSourcePath);

                foreach (array_slice($sourceDocument->getPages(), 0, 200) as $pageIndex => $sourcePage) {
                    $sourceTextRuns[$pageIndex + 1] = $sourcePage->getDataTm();
                }
            } catch (\Throwable $exception) {
                Log::warning('Could not read source PDF text positions for export highlights', [
                    'file' => $highlightedSourcePath,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

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

                if ($highlightedSourcePath !== null
                    && realpath($pdfPath) === realpath($highlightedSourcePath)
                    && isset($sourceTextRuns[$pageNumber])) {
                    $appliedSourceHighlights += $this->drawPdfTextHighlights(
                        $pdf,
                        $sourceTextRuns[$pageNumber],
                        (float) $size['height'],
                        $highlights,
                        $sourceIndexMap,
                    );
                }

                $importedPage = true;
            }
        }

        if (! $importedPage) {
            return false;
        }

        $pdf->Output('F', $outputPath);

        return is_file($outputPath) && filesize($outputPath) > 0;
    }

    private function drawPdfTextHighlights(
        Fpdi $pdf,
        array $textRuns,
        float $pageHeightMm,
        array $highlights,
        array $sourceIndexMap,
    ): int {
        $pointsToMm = 25.4 / 72;
        $pdf->SetLineWidth(0.65);
        $lines = [];
        $appliedHighlights = 0;

        foreach ($textRuns as $textRun) {
            if (! is_array($textRun) || ! isset($textRun[0], $textRun[1]) || ! is_array($textRun[0])) {
                continue;
            }

            $matrix = $textRun[0];
            $runText = trim((string) $textRun[1]);
            if ($runText === '' || ! isset($matrix[4], $matrix[5])) {
                continue;
            }

            // Avoid inaccurate marks for rotated/skewed text. The source page
            // remains intact; only unsupported text orientation skips a mark.
            if (abs((float) ($matrix[1] ?? 0)) > 0.01 || abs((float) ($matrix[2] ?? 0)) > 0.01) {
                continue;
            }

            $fontSizePt = max(4.0, (float) ($textRun[3] ?? 10));
            $baseline = (float) $matrix[5];
            $lineIndex = null;
            foreach ($lines as $index => $line) {
                if (abs($line['baseline'] - $baseline) <= max(2.0, $fontSizePt * 0.35)) {
                    $lineIndex = $index;
                    break;
                }
            }

            if ($lineIndex === null) {
                $lineIndex = count($lines);
                $lines[$lineIndex] = [
                    'baseline' => $baseline,
                    'text' => '',
                    'segments' => [],
                ];
            }

            $lineText = preg_replace('/\s+/u', ' ', $runText) ?? $runText;
            $lineText = trim($lineText);
            if ($lineText === '') {
                continue;
            }

            $separator = $lines[$lineIndex]['text'] === '' ? '' : ' ';
            $segmentStart = mb_strlen($lines[$lineIndex]['text'] . $separator);
            $lines[$lineIndex]['text'] .= $separator . $lineText;
            $lines[$lineIndex]['segments'][] = [
                'start' => $segmentStart,
                'end' => $segmentStart + mb_strlen($lineText),
                'x' => (float) $matrix[4],
                'baseline' => $baseline,
                'font_size' => $fontSizePt,
                'length' => max(1, mb_strlen($lineText)),
            ];
        }

        foreach ($lines as $line) {
            foreach ($highlights as $highlight) {
                $needle = trim((string) ($highlight->original_text ?? ''));
                if (mb_strlen($needle) < 4) {
                    continue;
                }

                $matches = $this->normalizedTextMatches($line['text'], $needle);
                if ($matches === []) {
                    continue;
                }

                $sourceId = $highlight->plagiarism_source_id ?? null;
                $sourceIndex = $sourceIndexMap[$sourceId] ?? 1;
                $color = (string) ($highlight->color_code ?? $highlight->source?->color_code ?? '#DE60E5');
                if (! preg_match('/^#?([0-9a-f]{6})$/i', $color, $colorMatch)) {
                    $color = '#DE60E5';
                    $colorMatch = ['#DE60E5', 'DE60E5'];
                }

                $hex = $colorMatch[1];
                [$red, $green, $blue] = [
                    hexdec(substr($hex, 0, 2)),
                    hexdec(substr($hex, 2, 2)),
                    hexdec(substr($hex, 4, 2)),
                ];
                $pdf->SetDrawColor($red, $green, $blue);
                $pdf->SetTextColor($red, $green, $blue);

                foreach ($matches as [$matchStart, $matchEnd]) {
                    $labelDrawn = false;
                    foreach ($line['segments'] as $segment) {
                        $start = max($matchStart, $segment['start']);
                        $end = min($matchEnd, $segment['end']);
                        if ($start >= $end) {
                            continue;
                        }

                        $segmentStart = $start - $segment['start'];
                        $segmentWidthPt = $segment['font_size'] * 0.5 * $segment['length'];
                        $xPt = $segment['x'] + ($segmentWidthPt * $segmentStart / $segment['length']);
                        $widthPt = max(2.0, $segmentWidthPt * ($end - $start) / $segment['length']);
                        $xMm = $xPt * $pointsToMm;
                        $yMm = $pageHeightMm - ($segment['baseline'] * $pointsToMm) + 0.8;
                        $pdf->Line($xMm, $yMm, $xMm + ($widthPt * $pointsToMm), $yMm);
                        $appliedHighlights++;

                        if ($sourceIndex > 0 && ! $labelDrawn) {
                            $pdf->SetFont('Arial', 'B', 6);
                            $pdf->Text(max(1, $xMm - 2), max(3, $yMm - 0.5), (string) $sourceIndex);
                            $labelDrawn = true;
                        }
                    }
                }

                // One line uses the first matching source to avoid stacked marks.
                break;
            }
        }

        return $appliedHighlights;
    }

    private function renderPartialPdf(string $view, array $data, string $outputPath): void
    {
        Pdf::loadView($view, $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Arial',
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

    private function ensureArialFont(): void
    {
        $fontDirectory = storage_path('app/fonts');
        File::ensureDirectoryExists($fontDirectory);

        foreach (['arial.ttf', 'arialbd.ttf'] as $fontName) {
            $fontPath = $fontDirectory . DIRECTORY_SEPARATOR . $fontName;
            $systemFont = 'C:\\Windows\\Fonts\\' . $fontName;

            if (! is_file($fontPath) && is_file($systemFont)) {
                @copy($systemFont, $fontPath);
            }
        }
    }

    private function mergePdfs(
        string $outputPath,
        string $coverPath,
        string $reportPath,
        ?string $sourceImagesManifest = null,
        ?string $sourcePath = null,
        ?string $highlightsManifest = null,
        ?string $submissionId = null
    ): bool {
        $pdf = new Fpdi();
        $importedPage = false;
        $sourcePdfPaths = [];

        if (is_file($coverPath) && filesize($coverPath) > 0) {
            $sourcePdfPaths[] = $coverPath;
        }

        if (is_string($sourcePath) && $sourcePath !== '' && is_file($sourcePath) && filesize($sourcePath) > 0) {
            $sourcePdfPaths[] = $sourcePath;
        }

        if (is_file($reportPath) && filesize($reportPath) > 0) {
            $sourcePdfPaths[] = $reportPath;
        }

        if ($sourceImagesManifest && is_file($sourceImagesManifest)) {
            $payload = json_decode((string) file_get_contents($sourceImagesManifest), true);
            $imagePages = is_array($payload) ? ($payload['pages'] ?? $payload) : [];
            foreach ($imagePages as $page) {
                $path = is_array($page) ? ($page['path'] ?? null) : $page;
                if (! is_string($path) || ! is_file($path)) {
                    continue;
                }

                [$width, $height] = getimagesize($path) ?: [595, 842];
                $pdf->AddPage('P', [$width, $height]);
                $pdf->Image($path, 0, 0, $width, $height, '', '', '', false, 300, '', false, false, 0, 'D');
                $importedPage = true;
            }
        }

        if ($sourcePdfPaths === [] && ! $importedPage) {
            return false;
        }

        foreach ($sourcePdfPaths as $pdfPath) {
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

}
