<?php

namespace Tests\Unit;

use App\Services\DocumentPageRenderer;
use App\Services\PlagiarismExportService;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

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

    public function test_heading_formatter_preserves_numbered_paragraph_hanging_indent(): void
    {
        $service = new PlagiarismExportService(new DocumentPageRenderer());
        $method = new ReflectionMethod($service, 'formatHeadingHierarchy');
        $method->setAccessible(true);
        $html = <<<'HTML'
  <p data-docx-heading-level="1">BAB I</p>
  <p data-docx-list-number="9." style="margin-left: 0.5000in !important; text-indent: -0.2500in !important;"><span class="doc-list-number" style="display: inline-block; width: 0.2500in;">9. </span>Adanya kesenjangan nilai.</p>
  HTML;

        $result = $method->invoke($service, $html);

        $this->assertStringContainsString('margin-left: 0.5000in !important', $result);
        $this->assertStringContainsString('text-indent: -0.2500in !important', $result);
    }

    public function test_heading_hierarchy_and_following_body_indents_are_applied(): void
    {
        $service = new PlagiarismExportService(new DocumentPageRenderer());
        $method = new ReflectionMethod($service, 'formatHeadingHierarchy');
        $method->setAccessible(true);

        $html = <<<'HTML'
<p data-docx-heading-level="1"><span style="font-size: 12pt">BAB I</span></p>
<p data-docx-heading-level="2" data-docx-style="Judul2"><span style="font-size: 12pt">Latar Belakang</span></p>
<p data-docx-style="TeksIsi"><span style="font-size: 12pt">Isi di bawah heading dua.</span></p>
<p data-docx-heading-level="3" data-docx-style="Judul3"><span style="font-size: 12pt">Manfaat teoritis</span></p>
<p data-docx-style="TeksIsi"><span style="font-size: 12pt">Isi di bawah heading tiga.</span></p>
<p data-docx-heading-level="4" data-docx-style="Judul4"><span style="font-size: 12pt">Pengertian Manajemen</span></p>
<p data-docx-style="TeksIsi"><span style="font-size: 12pt">Isi di bawah heading empat.</span></p>
<p data-docx-heading-level="2" data-docx-style="Judul2"><span style="font-size: 12pt">Identifikasi Masalah</span></p>
HTML;

        $result = $method->invoke($service, $html);

        $this->assertStringContainsString('text-align: center !important', $result);
        $this->assertStringContainsString('doc-heading-number', $result);
        $this->assertStringContainsString('>A.  </span>', $result);
        $this->assertStringContainsString('>1.  </span>', $result);
        $this->assertStringContainsString('>a)  </span>', $result);
        $this->assertStringContainsString('>B.  </span>', $result);
        $this->assertStringContainsString('margin-left: 0 !important; text-indent: 0.5in !important', $result);
        $this->assertStringContainsString('margin-left: 0.5in !important; text-indent: 0.5in !important', $result);
        $this->assertStringContainsString('margin-left: 1in !important; text-indent: 0.5in !important', $result);
        $this->assertStringContainsString('font-weight: bold !important', $result);
    }

    public function test_heading_three_and_four_number_markers_have_one_separator(): void
    {
        $service = new PlagiarismExportService(new DocumentPageRenderer());
        $method = new ReflectionMethod($service, 'formatHeadingHierarchy');
        $method->setAccessible(true);
        $html = <<<'HTML'
  <p data-docx-heading-level="1">BAB I</p>
  <p data-docx-heading-level="2">Bagian</p>
  <p data-docx-heading-level="3">Objek Penelitian</p>
  <p data-docx-heading-level="4">Jenis Kelamin</p>
  HTML;

        $result = $method->invoke($service, $html);
        $dom = new DOMDocument();
        @$dom->loadHTML($result);
        $xpath = new \DOMXPath($dom);
        $markers = $xpath->query('//span[contains(concat(" ", normalize-space(@class), " "), " doc-heading-number ")]');

        $this->assertSame('A.', trim($markers->item(0)->textContent));
        $this->assertSame('1.', trim($markers->item(1)->textContent));
        $this->assertSame('a)', trim($markers->item(2)->textContent));
        $this->assertStringNotContainsString('margin-right:', $markers->item(0)->getAttribute('style'));
        $this->assertStringNotContainsString('margin-right:', $markers->item(1)->getAttribute('style'));
        $this->assertStringNotContainsString('margin-right:', $markers->item(2)->getAttribute('style'));
        $this->assertStringContainsString('font-weight: bold !important', $markers->item(0)->getAttribute('style'));
        $this->assertStringContainsString('font-weight: bold !important', $markers->item(1)->getAttribute('style'));
        $this->assertStringContainsString('font-weight: normal !important', $markers->item(2)->getAttribute('style'));
        $this->assertStringContainsString('>1.  </span>', $result);
        $this->assertStringContainsString('>a)  </span>', $result);
    }

    public function test_docx_heading_styles_are_mapped_without_marking_table_of_contents_entries(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'heading_docx_');
        $this->assertNotFalse($path);

        $zip = new \ZipArchive();
        $this->assertSame(true, $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE));
        $zip->addFromString('word/document.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>
  <w:p><w:pPr><w:pStyle w:val="Heading1"/></w:pPr><w:r><w:t>Chapter One</w:t></w:r></w:p>
  <w:p><w:pPr><w:pStyle w:val="TOC1"/></w:pPr><w:r><w:t>A. Second Section 4</w:t></w:r></w:p>
  <w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>Second Section</w:t></w:r></w:p>
    <w:p><w:r><w:t>Body after heading two.</w:t></w:r></w:p>
  <w:p><w:pPr><w:pStyle w:val="Heading3"/></w:pPr><w:r><w:t>Third Section</w:t></w:r></w:p>
  <w:sectPr/>
</w:body></w:document>
XML);
        $zip->addFromString('word/styles.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:pPr><w:outlineLvl w:val="0"/></w:pPr></w:style>
  <w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/><w:pPr><w:outlineLvl w:val="1"/></w:pPr></w:style>
  <w:style w:type="paragraph" w:styleId="Heading3"><w:name w:val="heading 3"/><w:pPr><w:outlineLvl w:val="2"/></w:pPr></w:style>
  <w:style w:type="paragraph" w:styleId="TOC1"><w:name w:val="toc 1"/></w:style>
</w:styles>
XML);
        $zip->close();

        try {
            $renderer = new DocumentPageRenderer();
            $method = new ReflectionMethod($renderer, 'annotateDocxHeadingParagraphs');
            $method->setAccessible(true);
            $html = <<<'HTML'
<p>Chapter One</p>
<p>A. Second Section 4</p>
<p>Body after heading two.</p>
<p>Third Section</p>
HTML;

            $result = $method->invoke($renderer, $path, $html);
            $dom = new DOMDocument();
            @$dom->loadHTML($result);
            $xpath = new \DOMXPath($dom);
            $paragraphs = $xpath->query('//p');

            $this->assertSame('1', $paragraphs->item(0)->getAttribute('data-docx-heading-level'));
            $this->assertSame('', $paragraphs->item(1)->getAttribute('data-docx-heading-level'));
            $this->assertSame('2', $paragraphs->item(2)->getAttribute('data-docx-heading-level'));
            $this->assertSame('Second Section', trim($paragraphs->item(2)->textContent));
            $this->assertSame('Body after heading two.', trim($paragraphs->item(3)->textContent));
            $this->assertSame('3', $paragraphs->item(4)->getAttribute('data-docx-heading-level'));
        } finally {
            @unlink($path);
        }
    }

    public function test_docx_automatic_numbering_survives_split_html_and_uses_hanging_indent(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'numbering_docx_');
        $this->assertNotFalse($path);

        $zip = new \ZipArchive();
        $this->assertSame(true, $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE));
        $zip->addFromString('word/document.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>
  <w:p><w:pPr><w:pStyle w:val="Heading3"/><w:numPr><w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr></w:pPr><w:r><w:t>First item</w:t></w:r></w:p>
  <w:p><w:pPr><w:numPr><w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr></w:pPr><w:r><w:t>Second item</w:t></w:r></w:p>
  <w:p><w:pPr><w:pStyle w:val="Heading4"/><w:numPr><w:ilvl w:val="1"/><w:numId w:val="1"/></w:numPr></w:pPr><w:r><w:t>Nested item</w:t></w:r></w:p>
  <w:p><w:pPr><w:numPr><w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr></w:pPr><w:sdt><w:sdtPr/><w:sdtContent><w:r><w:t>Robbins &amp; Judge (2024)</w:t></w:r></w:sdtContent></w:sdt><w:r><w:t xml:space="preserve"> mendefinisikan konsep job insecurity secara utuh di instansi ini dengan kesinambungan penulisan tanpa pemisah yang tidak semestinya.</w:t></w:r></w:p>
  <w:sectPr/>
</w:body></w:document>
XML);
        $zip->addFromString('word/numbering.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<w:numbering xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:abstractNum w:abstractNumId="0">
    <w:lvl w:ilvl="0"><w:start w:val="1"/><w:numFmt w:val="decimal"/><w:lvlText w:val="%1."/><w:lvlJc w:val="left"/></w:lvl>
    <w:lvl w:ilvl="1"><w:start w:val="1"/><w:numFmt w:val="lowerLetter"/><w:lvlText w:val="%2)"/><w:lvlJc w:val="left"/><w:pPr><w:ind w:left="1440" w:hanging="360"/></w:pPr></w:lvl>
  </w:abstractNum>
  <w:num w:numId="1"><w:abstractNumId w:val="0"/></w:num>
</w:numbering>
XML);
        $zip->addFromString('word/styles.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:style w:type="paragraph" w:styleId="Heading3"><w:name w:val="heading 3"/><w:pPr><w:outlineLvl w:val="2"/></w:pPr></w:style>
  <w:style w:type="paragraph" w:styleId="Heading4"><w:name w:val="heading 4"/><w:pPr><w:outlineLvl w:val="3"/></w:pPr></w:style>
</w:styles>
XML);
        $zip->close();

        try {
            $renderer = new DocumentPageRenderer();
            $copyMethod = new ReflectionMethod($renderer, 'createDocxHtmlConversionCopy');
            $copyMethod->setAccessible(true);
            $conversionCopy = $copyMethod->invoke($renderer, $path, sys_get_temp_dir());
            $this->assertNotFalse($conversionCopy);
            $conversionZip = new \ZipArchive();
            $this->assertSame(true, $conversionZip->open($conversionCopy));
            $conversionXml = $conversionZip->getFromName('word/document.xml');
            $conversionZip->close();
            $this->assertIsString($conversionXml);
            $this->assertStringNotContainsString('<w:sdt', $conversionXml);
            $this->assertStringContainsString('Robbins &amp; Judge (2024)', $conversionXml);
            @unlink($conversionCopy);

            $method = new ReflectionMethod($renderer, 'annotateDocxHeadingParagraphs');
            $method->setAccessible(true);
            $html = <<<'HTML'
<p><span>First </span></p>
<p><span style="font-style: italic">item</span></p>
<p>Second</p>
<p>&nbsp;</p>
<p>item</p>
<p>Nested item</p>
<p><span>Robbins </span></p>
<p><span>&amp; </span></p>
<p><span>Judge </span></p>
<p><span>(2024) </span></p>
<p><span>men</span></p>
<p><span>definisikan </span></p>
<p><span>konsep </span></p>
<p><span>job </span></p>
<p><span>insecu</span></p>
<p><span>rity </span></p>
<p><span>se</span></p>
<p><span>cara </span></p>
<p><span>utuh </span></p>
<p><span>d</span></p>
<p><span>i </span></p>
<p><span>instansi </span></p>
<p><span>ini </span></p>
<p><span>dengan </span></p>
<p><span>ke</span></p>
<p><span>sin</span></p>
<p><span>ambungan </span></p>
<p><span>penu</span></p>
<p><span>lisan </span></p>
<p><span>tanpa </span></p>
<p><span>pemi</span></p>
<p><span>sah </span></p>
<p><span>yang </span></p>
<p><span>tidak </span></p>
<p><span>semes</span></p>
<p><span>tinya.</span></p>
HTML;

            $result = $method->invoke($renderer, $path, $html);
            $dom = new DOMDocument();
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $result);
            $xpath = new \DOMXPath($dom);
            $numberedParagraphs = $xpath->query('//p[@data-docx-list-number]');

            $this->assertSame(4, $numberedParagraphs->length);
            $markers = array_map(
              fn ($paragraph) => trim(str_replace("\u{00A0}", '', $paragraph->getElementsByTagName('span')->item(0)->textContent)),
                iterator_to_array($numberedParagraphs),
            );
            $this->assertSame(['1.', '2.', 'a)', '3.'], $markers);
            $itemText = fn ($index) => trim(str_replace("\u{00A0}", ' ', str_replace($markers[$index], '', $numberedParagraphs->item($index)->textContent)));
            $this->assertSame('First item', $itemText(0));
            $this->assertSame('Second item', $itemText(1));
            $this->assertSame('Nested item', $itemText(2));
            $this->assertSame(
                'Robbins & Judge (2024) mendefinisikan konsep job insecurity secara utuh di instansi ini dengan kesinambungan penulisan tanpa pemisah yang tidak semestinya.',
              trim(str_replace("\u{00A0}", ' ', str_replace($markers[3], '', $numberedParagraphs->item(3)->textContent))),
            );
            $this->assertStringContainsString('margin-left: 0.5000in !important', $numberedParagraphs->item(0)->getAttribute('style'));
            $this->assertStringContainsString('text-indent: -0.2083in !important', $numberedParagraphs->item(1)->getAttribute('style'));
            $ordinaryMarker = $numberedParagraphs->item(1)->getElementsByTagName('span')->item(0);
            $this->assertSame(2, substr_count($ordinaryMarker->textContent, "\u{00A0}"));
            $this->assertStringContainsString('display: inline-block', $ordinaryMarker->getAttribute('style'));
            $this->assertMatchesRegularExpression('/(?:width|min-width)\s*:\s*(?:\d+(?:\.\d+)?in|\d+(?:\.\d+)?ch)/i', $ordinaryMarker->getAttribute('style'));
            $this->assertStringContainsString('font-weight: bold !important', $numberedParagraphs->item(0)->getElementsByTagName('span')->item(0)->getAttribute('style'));
            $this->assertStringContainsString('font-weight: normal !important', $ordinaryMarker->getAttribute('style'));
            $this->assertStringContainsString('font-weight: normal !important', $numberedParagraphs->item(2)->getElementsByTagName('span')->item(0)->getAttribute('style'));
            $this->assertStringContainsString('margin-left: 1.0000in !important', $numberedParagraphs->item(2)->getAttribute('style'));
            $this->assertSame(4, $xpath->query('//p')->length);
        } finally {
            @unlink($path);
        }
    }

    public function test_split_heading_two_without_following_anchor_is_not_synthesized_twice(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'split_heading_docx_');
        $this->assertNotFalse($path);

        $zip = new \ZipArchive();
        $this->assertSame(true, $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE));
        $zip->addFromString('word/document.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>
  <w:p><w:pPr><w:pStyle w:val="Heading1"/></w:pPr><w:r><w:t>Chapter One</w:t></w:r></w:p>
  <w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>Earlier heading</w:t></w:r></w:p>
  <w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>Pembatasan Masalah</w:t></w:r></w:p>
  <w:sectPr/>
</w:body></w:document>
XML);
        $zip->addFromString('word/styles.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/></w:style>
  <w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/></w:style>
</w:styles>
XML);
        $zip->close();

        try {
            $renderer = new DocumentPageRenderer();
            $method = new ReflectionMethod($renderer, 'annotateDocxHeadingParagraphs');
            $method->setAccessible(true);
            $html = <<<'HTML'
<p>Chapter One</p>
<p>Pembatasan</p>
<p>&nbsp;</p>
<p>Masalah</p>
<p>Earlier heading</p>
HTML;

            $result = $method->invoke($renderer, $path, $html);
            $dom = new DOMDocument();
            @$dom->loadHTML($result);
            $xpath = new \DOMXPath($dom);
            $paragraphs = $xpath->query('//p');
            $headingTexts = [];
            foreach ($paragraphs as $paragraph) {
                if ($paragraph->getAttribute('data-docx-heading-level') === '2') {
                    $headingTexts[] = trim($paragraph->textContent);
                }
            }

            $this->assertSame(['Pembatasan Masalah', 'Earlier heading'], $headingTexts);
            $this->assertSame(1, substr_count($result, 'Pembatasan'));
            $this->assertStringNotContainsString('<p> </p>', $result);
            $this->assertStringNotContainsString('C. Pembatasan Masalah', $result);

            $formatMethod = new ReflectionMethod(new PlagiarismExportService($renderer), 'formatHeadingHierarchy');
            $formatMethod->setAccessible(true);
            $formatted = $formatMethod->invoke(new PlagiarismExportService($renderer), $result);
            $this->assertSame(1, substr_count($formatted, 'Pembatasan Masalah'));
        } finally {
            @unlink($path);
        }
    }

    public function test_docx_numbered_heading_does_not_duplicate_prefix_in_export_html(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'heading_numbering_docx_');
        $this->assertNotFalse($path);

        $zip = new \ZipArchive();
        $this->assertSame(true, $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE));
        $zip->addFromString('word/document.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>
  <w:p><w:pPr><w:pStyle w:val="Heading3"/><w:numPr><w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr></w:pPr><w:r><w:t>1. Judul coba</w:t></w:r></w:p>
  <w:sectPr/>
</w:body></w:document>
XML);
        $zip->addFromString('word/styles.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:style w:type="paragraph" w:styleId="Heading3"><w:name w:val="heading 3"/><w:pPr><w:outlineLvl w:val="2"/></w:pPr></w:style>
</w:styles>
XML);
        $zip->addFromString('word/numbering.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<w:numbering xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:abstractNum w:abstractNumId="0">
    <w:lvl w:ilvl="0"><w:start w:val="1"/><w:numFmt w:val="decimal"/><w:lvlText w:val="%1."/><w:lvlJc w:val="left"/></w:lvl>
  </w:abstractNum>
  <w:num w:numId="1"><w:abstractNumId w:val="0"/></w:num>
</w:numbering>
XML);
        $zip->close();

        try {
            $renderer = new DocumentPageRenderer();
            $method = new ReflectionMethod($renderer, 'annotateDocxHeadingParagraphs');
            $method->setAccessible(true);
            $html = <<<'HTML'
<p>Judul coba</p>
HTML;

            $result = $method->invoke($renderer, $path, $html);
            $this->assertStringContainsString('Judul coba', $result);
            $this->assertSame(1, substr_count(strtolower($result), 'judul coba'));
            $this->assertStringContainsString('doc-list-number', $result);
        } finally {
            @unlink($path);
        }
    }
}
