<?php

namespace Tests\Unit;

use App\Models\Document;
use App\Models\PlagiarismCheck;
use App\Services\DocumentPageRenderer;
use App\Services\PlagiarismExportService;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use setasign\Fpdi\Fpdi;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;

class PlagiarismExportServiceTest extends TestCase
{
    public function test_only_paragraphs_with_twelve_point_runs_are_forced_to_justify(): void
    {
        $service = new PlagiarismExportService(new DocumentPageRenderer());
        $method = new ReflectionMethod($service, 'justifyTwelvePointParagraphs');
        $method->setAccessible(true);

        $html = <<<'HTML'
<html><body>
    <p style="text-align: center; margin-bottom: 0"><span style="font-size: 12pt">12pt text</span></p>
    <p style="text-align: center"><span style="font-size: 11pt">11pt text</span></p>
</body></html>
HTML;

        $result = $method->invoke($service, $html);

        $this->assertMatchesRegularExpression('/<p style="margin-bottom: 0; text-align: justify !important;">/', $result);
        $this->assertStringContainsString('<span style="font-size: 12pt">12pt text</span>', $result);
        $this->assertStringContainsString('<p style="text-align: center"><span style="font-size: 11pt">11pt text</span></p>', $result);
    }

    public function test_heading_formatter_adds_numbering_and_body_indents(): void
    {
        $service = new PlagiarismExportService(new DocumentPageRenderer());
        $method = new ReflectionMethod($service, 'formatHeadingHierarchy');
        $method->setAccessible(true);
        $html = <<<'HTML'
<p data-docx-heading-level="1"><span style="font-size: 12pt">BAB I</span></p>
<p data-docx-heading-level="2" data-docx-style="Judul2"><span style="font-size: 12pt">Latar Belakang</span></p>
<p data-docx-style="TeksIsi"><span style="font-size: 12pt">Isi di bawah heading.</span></p>
HTML;

        $result = $method->invoke($service, $html);

        $this->assertStringContainsString('doc-heading-number', $result);
        $this->assertStringContainsString('>A.  </span>', $result);
        $this->assertStringContainsString('text-indent: 0.5in !important', $result);
    }

    public function test_pdf_merge_keeps_source_pages_and_applies_text_highlights(): void
    {
        $sourcePath = tempnam(sys_get_temp_dir(), 'source_pdf_');
        $outputPath = tempnam(sys_get_temp_dir(), 'merged_pdf_');
        $this->assertNotFalse($sourcePath);
        $this->assertNotFalse($outputPath);

        $sourceText = 'A distinctive plagiarism sentence appears here.';
        $source = new \Dompdf\Dompdf();
        $source->loadHtml('<html><body><p>' . $sourceText . '</p></body></html>');
        $source->render();
        file_put_contents($sourcePath, $source->output());

        try {
            $service = new PlagiarismExportService(new DocumentPageRenderer());
            $mergeMethod = new ReflectionMethod($service, 'mergePdfFiles');
            $mergeMethod->setAccessible(true);
            $highlight = (object) [
                'original_text' => 'distinctive plagiarism sentence',
                'plagiarism_source_id' => 7,
                'color_code' => '#DE60E5',
            ];

            $this->assertTrue($mergeMethod->invoke(
                $service,
                $outputPath,
                [$sourcePath],
                $sourcePath,
                [$highlight],
                [7 => 1],
            ));

            $mergedPdf = new Fpdi();
            $this->assertSame(1, $mergedPdf->setSourceFile($outputPath));
            $mergedText = (new Parser())->parseFile($outputPath)->getText();
            $this->assertStringContainsString($sourceText, $mergedText);

            $config = new Config();
            $config->setDataTmFontInfoHasToBeIncluded(true);
            $sourceRuns = (new Parser([], $config))->parseFile($sourcePath)->getPages()[0]->getDataTm();
            $drawPdf = new Fpdi();
            $drawPdf->AddPage();
            $drawMethod = new ReflectionMethod($service, 'drawPdfTextHighlights');
            $drawMethod->setAccessible(true);
            $this->assertGreaterThan(0, $drawMethod->invoke($service, $drawPdf, $sourceRuns, 297, [$highlight], [7 => 1]));
        } finally {
            @unlink($sourcePath);
            @unlink($outputPath);
        }
    }

    public function test_php_fallback_contains_stored_document_and_highlight_markup(): void
    {
        $service = new PlagiarismExportService(new DocumentPageRenderer());
        $method = new ReflectionMethod($service, 'buildFallbackHighlightedText');
        $method->setAccessible(true);

        $check = new PlagiarismCheck();
        $document = new Document();
        $document->content = 'Before match: distinctive plagiarism sentence. After match.';
        $highlight = (object) [
            'original_text' => 'distinctive plagiarism sentence',
            'start_position' => 14,
            'end_position' => 45,
            'plagiarism_source_id' => 7,
            'color_code' => '#DE60E5',
        ];
        $check->setRelation('document', $document);
        $check->setRelation('highlights', collect([$highlight]));

        $html = $method->invoke($service, $check, [7 => 2]);

        $this->assertStringContainsString('Before match:', $html);
        $this->assertStringContainsString('After match.', $html);
        $this->assertStringContainsString('class="t-highlight"', $html);
        $this->assertStringContainsString('>2</sup>', $html);
        $this->assertStringContainsString('distinctive plagiarism sentence', $html);
    }

    public function test_export_retry_prefers_extracted_text_and_drops_rich_docx_styles(): void
    {
        $service = new PlagiarismExportService(new DocumentPageRenderer());
        $method = new ReflectionMethod($service, 'prepareFallbackDocument');
        $method->setAccessible(true);

        $check = new PlagiarismCheck();
        $document = new Document();
        $document->content = 'Plain extracted text for PDF retry.';
        $check->setRelation('document', $document);
        $check->setRelation('highlights', collect());

        $fallback = $method->invoke(
            $service,
            $check,
            [],
            '<div>Complex converted DOCX HTML</div>',
            '.docx-style { page: page1; }',
        );

        $this->assertTrue($fallback['uses_extracted_text']);
        $this->assertStringContainsString('Plain extracted text for PDF retry.', $fallback['html']);
        $this->assertStringNotContainsString('Complex converted DOCX HTML', $fallback['html']);
        $this->assertSame('', $fallback['styles']);
    }

    public function test_export_retry_keeps_rich_html_when_extracted_text_is_unavailable(): void
    {
        $service = new PlagiarismExportService(new DocumentPageRenderer());
        $method = new ReflectionMethod($service, 'prepareFallbackDocument');
        $method->setAccessible(true);

        $check = new PlagiarismCheck();
        $document = new Document();
        $document->content = '';
        $check->setRelation('document', $document);
        $check->setRelation('highlights', collect());
        $richHtml = '<div>Only available document content</div>';
        $richStyles = '.docx-page { margin: 1in; }';

        $fallback = $method->invoke($service, $check, [], $richHtml, $richStyles);

        $this->assertFalse($fallback['uses_extracted_text']);
        $this->assertSame($richHtml, $fallback['html']);
        $this->assertSame($richStyles, $fallback['styles']);
    }

    public function test_large_document_fallback_is_split_into_bounded_chunks(): void
    {
        $service = new PlagiarismExportService(new DocumentPageRenderer());
        $method = new ReflectionMethod($service, 'buildFallbackHighlightedTextChunks');
        $method->setAccessible(true);

        $check = new PlagiarismCheck();
        $document = new Document();
        $document->content = str_repeat("Paragraf pendek untuk uji ekspor.\n", 1500);
        $check->setRelation('document', $document);
        $check->setRelation('highlights', collect());

        $chunks = $method->invoke($service, $check, []);

        $this->assertCount(3, $chunks);
        $this->assertLessThanOrEqual(40000, max(array_map('mb_strlen', $chunks)));
        $this->assertStringContainsString('Paragraf pendek untuk uji ekspor.', implode('', $chunks));
    }

    public function test_lightweight_mode_disables_raw_source_pdf_import_for_hosting_exports(): void
    {
        $service = new PlagiarismExportService(new DocumentPageRenderer());
        $method = new ReflectionMethod($service, 'shouldImportSourcePdfForExport');
        $method->setAccessible(true);

        $sourcePdfPath = tempnam(sys_get_temp_dir(), 'source_pdf_');
        $this->assertNotFalse($sourcePdfPath);

        file_put_contents($sourcePdfPath, '%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF');

        $this->assertFalse($method->invoke($service, $sourcePdfPath, true));
        $this->assertTrue($method->invoke($service, $sourcePdfPath, false));

        @unlink($sourcePdfPath);
    }

    public function test_export_cache_uses_a_new_versioned_path_after_pdf_template_changes(): void
    {
        $service = new PlagiarismExportService(new DocumentPageRenderer());
        $check = new PlagiarismCheck();
        $check->id = 42;

        $this->assertSame('exports/v5/plagiarism_42.pdf', $service->getExportDiskPath($check));
    }

    public function test_bundled_korean_font_renders_the_cover_metadata_without_replacement_glyphs(): void
    {
        $projectRoot = dirname(__DIR__, 2);
        $fontPath = str_replace('\\', '/', $projectRoot . '/resources/fonts/NotoSansKR[wght].ttf');
        $phrase = '논문 및 과제 검사 - 유사도 검사 시 DB 미 저장';
        $outputPath = tempnam(sys_get_temp_dir(), 'korean_cover_');
        $this->assertNotFalse($outputPath);

        try {
            $dompdf = new \Dompdf\Dompdf(['chroot' => $projectRoot]);
            $dompdf->loadHtml('<!doctype html><meta charset="utf-8"><style>@font-face{font-family:NotoSansKR;src:url("'
                . $fontPath
                . '") format("truetype");}.metadata{font-family:NotoSansKR;font-size:12px;}</style><p class="metadata">'
                . htmlspecialchars($phrase, ENT_QUOTES, 'UTF-8')
                . '</p>');
            $dompdf->render();
            file_put_contents($outputPath, $dompdf->output());

            $renderedText = (new Parser())->parseFile($outputPath)->getText();
            $this->assertStringContainsString($phrase, $renderedText);
            $this->assertStringNotContainsString('?', $renderedText);
        } finally {
            @unlink($outputPath);
        }
    }
}
