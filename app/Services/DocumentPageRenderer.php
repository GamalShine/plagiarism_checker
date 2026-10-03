<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

class DocumentPageRenderer
{
    private const MAX_PAGES = 200;
    private const DOCX_RENDER_VERSION = 'word-com-highlight-v12';

    public function renderPages(string $filePath, int $documentId): array
    {
        return [];
    }

    public function resolveSourcePdf(string $filePath, int $documentId): ?string
    {
        if (!is_file($filePath)) {
            return null;
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($extension === 'pdf') {
            return $filePath;
        }

        if ($extension !== 'docx') {
            return null;
        }

        $cacheDir = $this->cacheDirectory($documentId, $filePath);
        $cachedPdf = $cacheDir . DIRECTORY_SEPARATOR . 'source.pdf';

        if (is_file($cachedPdf) && filesize($cachedPdf) > 0) {
            return $cachedPdf;
        }

        File::ensureDirectoryExists($cacheDir);

        $pdfPath = $this->convertDocxToPdf($filePath, $cacheDir);
        if (!$pdfPath) {
            return null;
        }

        if ($pdfPath !== $cachedPdf) {
            @copy($pdfPath, $cachedPdf);
        }

        return is_file($cachedPdf) ? $cachedPdf : $pdfPath;
    }

    public function resolveSourcePdfWithoutShell(string $filePath, int $documentId, array $highlights = []): ?string
    {
        if (! is_file($filePath)) {
            return null;
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($extension === 'pdf') {
            return $filePath;
        }

        if ($extension !== 'docx') {
            return null;
        }

        $cacheDir = $this->cacheDirectory($documentId, $filePath);
        $highlightedCacheKey = md5(json_encode(array_map(
            fn ($highlight) => [
                'text' => (string) ($highlight->original_text ?? ''),
                'color' => (string) ($highlight->color_code ?? $highlight->source?->color_code ?? 'transparent'),
            ],
            $highlights,
        ),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        ) . '|highlight-render-v4');
        $cachedPdf = $highlights !== []
            ? $cacheDir . DIRECTORY_SEPARATOR . 'highlighted-source-' . $highlightedCacheKey . '.pdf'
            : $cacheDir . DIRECTORY_SEPARATOR . 'phpword-source.pdf';

        if (is_file($cachedPdf) && filesize($cachedPdf) > 0) {
            return $cachedPdf;
        }

        File::ensureDirectoryExists($cacheDir);

        $pdfPath = null;
        if (PHP_OS_FAMILY === 'Windows' && $highlights !== []) {
            $pdfPath = $this->convertDocxWithWordHighlights($filePath, $cacheDir, $highlights);
        }

        if (! $pdfPath) {
            $pdfPath = $this->convertDocxToPdf($filePath, $cacheDir);
        }

        if ($pdfPath && $pdfPath !== $cachedPdf && is_file($pdfPath)) {
            @copy($pdfPath, $cachedPdf);
        }

        return is_file($cachedPdf) && filesize($cachedPdf) > 0 ? $cachedPdf : null;
    }

    public function renderDocxHtml(string $filePath, int $documentId): ?string
    {
        if (! is_file($filePath) || strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) !== 'docx') {
            return null;
        }

        $cacheDir = $this->cacheDirectory($documentId, $filePath);
        $htmlPath = $cacheDir . DIRECTORY_SEPARATOR . 'document.html';
        $annotatedHtmlPath = $cacheDir . DIRECTORY_SEPARATOR . 'document-annotated.html';

        File::ensureDirectoryExists($cacheDir);

        try {
            if (is_file($annotatedHtmlPath) && filesize($annotatedHtmlPath) > 0) {
                $cachedHtml = @file_get_contents($annotatedHtmlPath);
                if ($cachedHtml !== false) {
                    return $cachedHtml;
                }
            }

            if (! is_file($htmlPath) || filesize($htmlPath) <= 0) {
                $conversionCopy = $this->createDocxHtmlConversionCopy($filePath, $cacheDir);
                try {
                    $phpWord = IOFactory::load($conversionCopy ?? $filePath);
                    IOFactory::createWriter($phpWord, 'HTML')->save($htmlPath);
                } finally {
                    if ($conversionCopy !== null) {
                        @unlink($conversionCopy);
                    }
                }
            }

            if (! is_file($htmlPath) || filesize($htmlPath) <= 0) {
                return null;
            }

            $html = (string) file_get_contents($htmlPath);
            $dom = new \DOMDocument('1.0', 'UTF-8');
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
            $body = $dom->getElementsByTagName('body')->item(0);
            if (! $body) {
                $cachedBodyHtml = $html;
                @file_put_contents($annotatedHtmlPath, $cachedBodyHtml);

                return $cachedBodyHtml;
            }

            $styles = '';
            foreach ($dom->getElementsByTagName('style') as $style) {
                $styleHtml = $dom->saveHTML($style);
                $styleHtml = preg_replace(
                    '/(?:page-break|break)-(?:before|after|inside)\s*:\s*[^;}]+;?/i',
                    '',
                    $styleHtml,
                );
                $styleHtml = preg_replace('/@page\b[^{}]*\{[^}]*\}/i', '', $styleHtml);
                $styles .= (string) $styleHtml;
            }

            foreach ($body->getElementsByTagName('img') as $image) {
                $src = (string) $image->getAttribute('src');
                if ($src !== '' && ! preg_match('/^(?:[a-z]+:|\/)/i', $src)) {
                    $image->setAttribute('src', str_replace('\\', '/', $cacheDir . DIRECTORY_SEPARATOR . $src));
                }
            }

            $bodyHtml = '';
            foreach ($body->childNodes as $child) {
                $bodyHtml .= $dom->saveHTML($child);
            }

            $bodyHtml = preg_replace(
                '/\s*(?:page-break|break)-(?:before|after|inside)\s*:\s*[^;"\']+;?/i',
                '',
                $bodyHtml,
            );
            $bodyHtml = preg_replace('/\s*page\s*:\s*page\d+\s*;?/i', '', $bodyHtml);

            if ($bodyHtml === '') {
                @file_put_contents($annotatedHtmlPath, $html);

                return $html;
            }

            $processedHtml = $this->annotateDocxHeadingParagraphs($filePath, $bodyHtml);
            $finalHtml = $styles . $processedHtml;
            @file_put_contents($annotatedHtmlPath, $finalHtml);

            return $finalHtml;
        } catch (\Throwable $e) {
            Log::warning('DOCX to HTML conversion failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function annotateDocxHeadingParagraphs(string $filePath, string $bodyHtml): string
    {
        if (! class_exists(\ZipArchive::class) || ! class_exists(\DOMDocument::class)) {
            return $bodyHtml;
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return $bodyHtml;
        }

        try {
            $documentXml = $zip->getFromName('word/document.xml');
            $stylesXml = $zip->getFromName('word/styles.xml');
            $numberingXml = $zip->getFromName('word/numbering.xml');
            if ($documentXml === false || $stylesXml === false) {
                return $bodyHtml;
            }

            $sourceDocument = new \DOMDocument('1.0', 'UTF-8');
            $stylesDocument = new \DOMDocument('1.0', 'UTF-8');
            if (! @$sourceDocument->loadXML($documentXml) || ! @$stylesDocument->loadXML($stylesXml)) {
                return $bodyHtml;
            }

            $namespace = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
            $sourceXPath = new \DOMXPath($sourceDocument);
            $sourceXPath->registerNamespace('w', $namespace);
            $stylesXPath = new \DOMXPath($stylesDocument);
            $stylesXPath->registerNamespace('w', $namespace);
            $styleLevels = [];
            $listEntries = $this->extractWordListEntries($sourceDocument, $numberingXml === false ? null : $numberingXml);

            foreach ($stylesXPath->query('//w:style[@w:type="paragraph"]') ?: [] as $style) {
                $styleId = $style->getAttribute('w:styleId');
                $name = $stylesXPath->query('./w:name', $style)->item(0)?->getAttribute('w:val') ?? '';
                $outline = $stylesXPath->query('./w:pPr/w:outlineLvl', $style)->item(0)?->getAttribute('w:val');
                $normalized = mb_strtolower(preg_replace('/[\s_-]+/u', '', $styleId) ?? $styleId);
                $normalizedName = mb_strtolower(preg_replace('/[\s_-]+/u', '', $name) ?? $name);
                if (preg_match('/^(?:heading|judul)([1-4])$/u', $normalized, $matches)
                    || preg_match('/^(?:heading|judul)([1-4])$/u', $normalizedName, $matches)) {
                    $styleLevels[$styleId] = (int) $matches[1];
                } elseif ($outline !== null && (int) $outline <= 3) {
                    $styleLevels[$styleId] = (int) $outline + 1;
                }
            }

            $headings = [];
            $sourceParagraphs = [];
            $sourceHeadingLevels = [];
            foreach ($sourceXPath->query('//w:body//w:p') ?: [] as $sourceIndex => $paragraph) {
                $text = '';
                foreach ($sourceXPath->query('.//w:t | .//w:tab | .//w:br', $paragraph) ?: [] as $textNode) {
                    $text .= $textNode->localName === 't' ? $textNode->textContent : ' ';
                }

                $styleNode = $sourceXPath->query('./w:pPr/w:pStyle', $paragraph)->item(0);
                $outlineNode = $sourceXPath->query('./w:pPr/w:outlineLvl', $paragraph)->item(0);
                $styleId = $styleNode?->getAttribute('w:val') ?? '';
                $level = $outlineNode && (int) $outlineNode->getAttribute('w:val') <= 3
                    ? (int) $outlineNode->getAttribute('w:val') + 1
                    : ($styleLevels[$styleId] ?? null);
                $normalizedText = $this->normalizeParagraphText($text);
                $sourceParagraphs[] = ['text' => $normalizedText, 'level' => $level, 'style' => $styleId];

                if ($level !== null && $normalizedText !== '') {
                    $sourceHeadingLevels[$sourceIndex] = $level;
                    $cleanText = $this->normalizeParagraphText($this->stripLeadingListMarker(trim($text)));
                    $headings[] = [
                        'text' => $cleanText !== '' ? $cleanText : $normalizedText,
                        'original_text' => $this->stripLeadingListMarker(trim($text)),
                        'style' => $styleId,
                        'level' => $level,
                        'source_index' => $sourceIndex,
                    ];
                }
            }

            $htmlDocument = new \DOMDocument('1.0', 'UTF-8');
            @$htmlDocument->loadHTML('<?xml encoding="UTF-8"><div id="docx-export-root">' . $bodyHtml . '</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
            $root = $htmlDocument->getElementById('docx-export-root');
            if (! $root) {
                return $bodyHtml;
            }

            $htmlXPath = new \DOMXPath($htmlDocument);
            $paragraphs = iterator_to_array($htmlXPath->query('.//p', $root) ?: []);
            $used = [];
            $lastMatchedListHtmlIndex = -1;

            foreach ($listEntries as $entry) {
                $targetStart = null;
                $targetEnd = null;
                $targetText = $this->normalizeListMatchText($entry['text']);
                $count = count($paragraphs);

                for ($i = $lastMatchedListHtmlIndex + 1; $i < $count && $targetStart === null; $i++) {
                    if (isset($used[$i])) {
                        continue;
                    }

                    if ($paragraphs[$i]->hasAttribute('data-docx-heading-level')) {
                        continue;
                    }

                    $candidate = $this->normalizeListMatchText($this->stripLeadingListMarker($paragraphs[$i]->textContent ?? ''));
                    if ($candidate === $targetText) {
                        $targetStart = $targetEnd = $i;
                        break;
                    }

                    $combined = '';
                    for ($j = $i; $j < $count; $j++) {
                        if (isset($used[$j])
                            || $paragraphs[$j]->hasAttribute('data-docx-heading-level')
                            || $paragraphs[$j]->parentNode !== $paragraphs[$i]->parentNode) {
                            break;
                        }

                        $fragment = (string) ($paragraphs[$j]->textContent ?? '');
                        if ($this->normalizeParagraphText($fragment) === '') {
                            $combined .= ' ';
                        } else {
                            $combined .= $fragment;
                        }

                        $combinedText = $this->normalizeListMatchText($combined);
                        if ($combinedText === $targetText) {
                            $targetStart = $i;
                            $targetEnd = $j;
                            break;
                        }
                        if ($combinedText !== '' && ! str_starts_with($targetText, $combinedText)) {
                            break;
                        }
                    }
                }

                if ($targetStart === null) {
                    continue;
                }

                $paragraph = $paragraphs[$targetStart];
                if ($targetEnd > $targetStart) {
                    for ($i = $targetStart + 1; $i <= $targetEnd; $i++) {
                        $next = $paragraphs[$i];
                        if ($this->normalizeParagraphText($next->textContent ?? '') === '') {
                            $hasFollowingText = false;
                            for ($j = $i + 1; $j <= $targetEnd; $j++) {
                                if ($this->normalizeParagraphText($paragraphs[$j]->textContent ?? '') !== '') {
                                    $hasFollowingText = true;
                                    break;
                                }
                            }
                            if ($hasFollowingText && ! preg_match('/\s$/u', $paragraph->textContent ?? '')) {
                                $paragraph->appendChild($htmlDocument->createTextNode(' '));
                            }
                        } else {
                            while ($next->firstChild) {
                                $paragraph->appendChild($next->firstChild);
                            }
                        }
                        $next->parentNode?->removeChild($next);
                    }
                    array_splice($paragraphs, $targetStart + 1, $targetEnd - $targetStart);
                    array_splice($used, $targetStart + 1, $targetEnd - $targetStart);
                }

                if (! $paragraph->hasAttribute('data-docx-list-number')) {
                    $paragraphText = $this->stripLeadingListMarker($paragraph->textContent ?? '');
                    if ($paragraphText !== ($paragraph->textContent ?? '')) {
                        $paragraph->textContent = $paragraphText;
                    }

                    $listNumber = $htmlDocument->createElement('span');
                    $listNumber->setAttribute('class', 'doc-list-number');
                    $markerWidth = max(0, (int) $entry['indent_left'] - (int) $entry['indent_hanging']);
                    $markerWidthInches = number_format($markerWidth / 1440, 4, '.', '');
                    $headingLevel = $sourceHeadingLevels[$entry['source_index']] ?? null;
                    $markerWeight = in_array($headingLevel, [2, 3], true) ? 'bold' : 'normal';
                    $isOrdinaryList = $headingLevel === null;
                    $markerWidthStyle = 'display: inline-block; min-width: ' . (trim($markerWidthInches) !== '' ? $markerWidthInches . 'in' : '0.5in') . '; white-space: nowrap;';
                    if ($isOrdinaryList) {
                        $listNumber->setAttribute('style', 'font-weight: normal !important; ' . $markerWidthStyle);
                        $listNumber->appendChild($htmlDocument->createTextNode($entry['label'] . "\u{00A0}\u{00A0}"));
                    } else {
                        $listNumber->setAttribute('style', 'font-weight: ' . $markerWeight . ' !important; ' . $markerWidthStyle);
                        $listNumber->appendChild($htmlDocument->createTextNode($entry['label'] . '  '));
                    }
                    $paragraph->insertBefore($listNumber, $paragraph->firstChild);
                    $paragraph->setAttribute('data-docx-list-number', $entry['label']);
                    $leftInches = number_format((int) $entry['indent_left'] / 1440, 4, '.', '');
                    $hangingTwips = (int) $entry['indent_hanging'];
                    if ($isOrdinaryList) {
                        preg_match_all('/[\p{L}\p{N}]/u', $entry['label'], $wordCharacters);
                        preg_match_all('/[^\p{L}\p{N}]/u', $entry['label'], $punctuation);
                        $hangingTwips = (count($wordCharacters[0]) * 120)
                            + (count($punctuation[0]) * 60)
                            + 120; // two spaces at approximately one quarter-em each
                    }
                    $hangingInches = number_format($hangingTwips / 1440, 4, '.', '');
                    $paragraphStyle = (string) $paragraph->getAttribute('style');
                    $paragraphStyle = $this->replaceInlineStyleProperty($paragraphStyle, 'margin-left', $leftInches . 'in');
                    $paragraphStyle = $this->replaceInlineStyleProperty($paragraphStyle, 'text-indent', '-' . $hangingInches . 'in');
                    $paragraph->setAttribute('style', $paragraphStyle);
                    $used[$targetStart] = true;
                }

                $lastMatchedListHtmlIndex = $targetStart;
            }

            foreach ($paragraphs as $index => $paragraph) {
                if ($paragraph->hasAttribute('data-docx-list-number')) {
                    $used[$index] = true;
                }
            }

            $lastMatchedHtmlIndex = -1;
            foreach ($headings as $heading) {
                foreach ($paragraphs as $index => $paragraph) {
                    if ($paragraph->hasAttribute('data-docx-list-number')
                        && $this->normalizeParagraphText($this->stripLeadingListMarker($paragraph->textContent ?? '')) === $heading['text']) {
                        $used[$index] = true;
                        continue 2;
                    }
                }

                $start = null;
                $end = null;
                $count = count($paragraphs);

                // Exact text matching prevents ordinary prose that mentions a heading from being styled.
                for ($i = $lastMatchedHtmlIndex + 1; $i < $count; $i++) {
                    if (! isset($used[$i]) && $this->normalizeParagraphText($paragraphs[$i]->textContent) === $heading['text']) {
                        $start = $end = $i;
                        break;
                    }
                }

                // PHPWord can split one Word paragraph across adjacent HTML paragraphs; reunite only an exact text match.
                if ($start === null) {
                    for ($i = $lastMatchedHtmlIndex + 1; $i < $count && $start === null; $i++) {
                        if (isset($used[$i])) {
                            continue;
                        }
                        $combined = '';
                        for ($j = $i; $j < min($count, $i + 5); $j++) {
                            if (isset($used[$j])) {
                                break;
                            }
                            $part = $this->normalizeParagraphText($paragraphs[$j]->textContent);
                            if ($part === '') {
                                continue;
                            }
                            $combined = trim($combined . ' ' . $part);
                            if ($combined === $heading['text']) {
                                $start = $i;
                                $end = $j;
                                break;
                            }
                            if (! str_starts_with($heading['text'], $combined)) {
                                break;
                            }
                        }
                    }
                }

                $anchorIndex = null;
                if ($start === null) {
                    for ($sourceIndex = $heading['source_index'] + 1, $sourceCount = count($sourceParagraphs); $sourceIndex < $sourceCount && $anchorIndex === null; $sourceIndex++) {
                        $nextText = $sourceParagraphs[$sourceIndex]['text'];
                        if ($nextText === '') {
                            continue;
                        }

                        for ($i = $lastMatchedHtmlIndex + 1; $i < $count; $i++) {
                            if (! isset($used[$i]) && $this->normalizeParagraphText($paragraphs[$i]->textContent) === $nextText) {
                                $anchorIndex = $i;
                                break;
                            }
                        }
                    }

                    // A prior heading may have advanced the cursor past this heading's converted text.
                    // Recover split title fragments immediately before the next source paragraph, rather
                    // than adding a synthetic heading while leaving the original words behind.
                    if ($anchorIndex !== null) {
                        $searchStart = max(0, $anchorIndex - 5);
                        for ($i = $searchStart; $i < $anchorIndex && $start === null; $i++) {
                            if (isset($used[$i])) {
                                continue;
                            }

                            $combined = '';
                            for ($j = $i; $j < $anchorIndex; $j++) {
                                if (isset($used[$j])) {
                                    break;
                                }
                                $part = $this->normalizeParagraphText($paragraphs[$j]->textContent);
                                if ($part === '') {
                                    continue;
                                }
                                $combined = trim($combined . ' ' . $part);
                                if ($combined === $heading['text']) {
                                    if ($j > $i) {
                                        $start = $i;
                                        $end = $j;
                                    }
                                    break;
                                }
                                if (! str_starts_with($heading['text'], $combined)) {
                                    break;
                                }
                            }
                        }
                    }
                }

                // Converters can reorder headings relative to surrounding content or omit a usable
                // following-paragraph anchor. In that case, recover the exact title (including titles
                // split across adjacent <p> elements) anywhere in the remaining unclaimed HTML before
                // synthesizing a new heading; otherwise the original fragments remain visible as duplicates.
                if ($start === null) {
                    for ($i = 0; $i < $count && $start === null; $i++) {
                        if ($paragraphs[$i]->hasAttribute('data-docx-list-number')
                            || $paragraphs[$i]->hasAttribute('data-docx-heading-level')) {
                            continue;
                        }

                        $combined = '';
                        for ($j = $i; $j < min($count, $i + 5); $j++) {
                            if ($paragraphs[$j]->hasAttribute('data-docx-list-number')
                                || $paragraphs[$j]->hasAttribute('data-docx-heading-level')) {
                                break;
                            }
                            $part = $this->normalizeParagraphText($paragraphs[$j]->textContent);
                            if ($part === '') {
                                continue;
                            }
                            $combined = trim($combined . ' ' . $part);
                            if ($combined === $heading['text']) {
                                $start = $i;
                                $end = $j;
                                break;
                            }
                            if (! str_starts_with($heading['text'], $combined)) {
                                break;
                            }
                        }
                    }
                }

                if ($start === null) {
                    $paragraph = $htmlDocument->createElement('p');
                    $paragraph->setAttribute('data-docx-heading-level', (string) $heading['level']);
                    $paragraph->setAttribute('data-docx-style', $heading['style']);
                    $paragraph->setAttribute('style', 'font-size: 12pt;');
                    $paragraph->appendChild($htmlDocument->createTextNode($heading['original_text']));

                    if ($anchorIndex !== null) {
                        $anchor = $paragraphs[$anchorIndex];
                        $anchor->parentNode?->insertBefore($paragraph, $anchor);
                        $lastMatchedHtmlIndex = $anchorIndex - 1;
                    } else {
                        $root->appendChild($paragraph);
                        $lastMatchedHtmlIndex = $count - 1;
                    }
                    continue;
                }

                $paragraph = $paragraphs[$start];
                if ($end > $start) {
                    for ($i = $start + 1; $i <= $end; $i++) {
                        $next = $paragraphs[$i];
                        if ($this->normalizeParagraphText($next->textContent) !== '') {
                            if ($this->normalizeParagraphText($paragraph->textContent) !== '') {
                                $paragraph->appendChild($htmlDocument->createTextNode(' '));
                            }
                            while ($next->firstChild) {
                                $paragraph->appendChild($next->firstChild);
                            }
                        }
                        $next->parentNode?->removeChild($next);
                    }
                    array_splice($paragraphs, $start + 1, $end - $start);
                    $end = $start;
                }

                $paragraph->setAttribute('data-docx-heading-level', (string) $heading['level']);
                if ($heading['style'] !== '') {
                    $paragraph->setAttribute('data-docx-style', $heading['style']);
                }
                for ($i = $start; $i <= $end; $i++) {
                    $used[$i] = true;
                }
                $lastMatchedHtmlIndex = $end;
            }

            $result = '';
            foreach ($root->childNodes as $child) {
                $result .= $htmlDocument->saveHTML($child);
            }

            return $result !== '' ? $result : $bodyHtml;
        } catch (\Throwable $e) {
            Log::warning('DOCX heading metadata extraction failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);

            return $bodyHtml;
        } finally {
            $zip->close();
        }
    }

    private function createDocxHtmlConversionCopy(string $filePath, string $cacheDir): ?string
    {
        if (! class_exists(\ZipArchive::class) || ! class_exists(\DOMDocument::class)) {
            return null;
        }

        $temporaryBase = tempnam($cacheDir, 'docx_html_');
        if ($temporaryBase === false) {
            return null;
        }

        $temporaryPath = $temporaryBase . '.docx';
        @unlink($temporaryBase);
        if (! @copy($filePath, $temporaryPath)) {
            @unlink($temporaryPath);

            return null;
        }

        $zip = new \ZipArchive();
        if ($zip->open($temporaryPath) !== true) {
            @unlink($temporaryPath);

            return null;
        }

        $conversionCopyReady = false;
        try {
            $documentXml = $zip->getFromName('word/document.xml');
            if ($documentXml === false) {
                return null;
            }

            $unwrappedXml = $this->unwrapDocxContentControls($documentXml);
            if ($unwrappedXml === null || $unwrappedXml === $documentXml) {
                return null;
            }

            if (! $zip->addFromString('word/document.xml', $unwrappedXml)) {
                return null;
            }

            $conversionCopyReady = true;

            return $temporaryPath;
        } finally {
            $zip->close();
            if (! $conversionCopyReady) {
                @unlink($temporaryPath);
            }
        }
    }

    private function unwrapDocxContentControls(string $documentXml): ?string
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        if (! @$document->loadXML($documentXml)) {
            return null;
        }

        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $controls = iterator_to_array($xpath->query('//w:sdt') ?: []);
        if ($controls === []) {
            return $documentXml;
        }

        foreach (array_reverse($controls) as $control) {
            $content = $xpath->query('./w:sdtContent', $control)->item(0);
            $parent = $control->parentNode;
            if (! $content || ! $parent) {
                continue;
            }

            while ($content->firstChild) {
                $parent->insertBefore($content->firstChild, $control);
            }
            $parent->removeChild($control);
        }

        return $document->saveXML() ?: null;
    }

    private function extractWordListEntries(
        \DOMDocument $sourceDocument,
        ?string $numberingXml,
    ): array {
        if ($numberingXml === null || trim($numberingXml) === '') {
            return [];
        }

        $numberingDocument = new \DOMDocument('1.0', 'UTF-8');
        if (! @$numberingDocument->loadXML($numberingXml)) {
            return [];
        }

        $namespace = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
        $numberingXPath = new \DOMXPath($numberingDocument);
        $numberingXPath->registerNamespace('w', $namespace);

        $abstractNums = [];
        foreach ($numberingXPath->query('//w:abstractNum') ?: [] as $abstractNum) {
            $abstractId = $abstractNum->getAttribute('w:abstractNumId');
            if ($abstractId === '') {
                continue;
            }

            $levels = [];
            foreach ($numberingXPath->query('./w:lvl', $abstractNum) ?: [] as $levelNode) {
                $ilvl = (int) ($levelNode->getAttribute('w:ilvl') ?: 0);
                $numFmt = $numberingXPath->query('./w:numFmt', $levelNode)->item(0)?->getAttribute('w:val') ?? 'decimal';
                $lvlText = $numberingXPath->query('./w:lvlText', $levelNode)->item(0)?->getAttribute('w:val') ?? '';
                $startValue = $numberingXPath->query('./w:start', $levelNode)->item(0)?->getAttribute('w:val') ?? '1';
                $indentNode = $numberingXPath->query('./w:pPr/w:ind', $levelNode)->item(0);

                $levels[$ilvl] = [
                    'format' => $numFmt,
                    'pattern' => $lvlText !== '' ? $lvlText : $this->defaultListPattern($numFmt),
                    'start' => max(1, (int) $startValue),
                    'indent_left' => max(0, (int) ($indentNode?->getAttribute('w:left') ?: 720)),
                    'indent_hanging' => max(0, (int) ($indentNode?->getAttribute('w:hanging') ?: 360)),
                ];
            }

            $abstractNums[(int) $abstractId] = $levels;
        }

        $numIdMap = [];
        foreach ($numberingXPath->query('//w:num') ?: [] as $num) {
            $numId = $num->getAttribute('w:numId');
            $abstractId = $numberingXPath->query('./w:abstractNumId', $num)->item(0)?->getAttribute('w:val') ?? '';
            if ($numId !== '' && $abstractId !== '') {
                $numIdMap[(int) $numId] = (int) $abstractId;
            }
        }

        $sourceXPath = new \DOMXPath($sourceDocument);
        $sourceXPath->registerNamespace('w', $namespace);

        $counterMap = [];
        $entries = [];
        foreach ($sourceXPath->query('//w:body//w:p') ?: [] as $sourceIndex => $paragraph) {
            $numPr = $sourceXPath->query('./w:pPr/w:numPr', $paragraph)->item(0);
            if (! $numPr) {
                continue;
            }

            $numIdNode = $sourceXPath->query('./w:numId', $numPr)->item(0);
            $ilvlNode = $sourceXPath->query('./w:ilvl', $numPr)->item(0);
            $numId = (int) ($numIdNode?->getAttribute('w:val') ?? 0);
            $ilvl = (int) ($ilvlNode?->getAttribute('w:val') ?? 0);

            $abstractId = $numIdMap[$numId] ?? null;
            $levelDefinition = $abstractId !== null ? ($abstractNums[$abstractId][$ilvl] ?? null) : null;
            if ($levelDefinition === null) {
                continue;
            }

            $counterKey = $numId . ':' . $ilvl;
            $counterMap[$counterKey] = ($counterMap[$counterKey] ?? ($levelDefinition['start'] - 1)) + 1;

            $text = '';
            foreach ($sourceXPath->query('.//w:t | .//w:tab | .//w:br', $paragraph) ?: [] as $textNode) {
                $text .= $textNode->localName === 't' ? $textNode->textContent : ' ';
            }

            $cleanText = $this->stripLeadingListMarker(trim($text));
            $normalizedText = $this->normalizeParagraphText($cleanText);
            if ($normalizedText === '') {
                continue;
            }

            $paragraphIndent = $sourceXPath->query('./w:pPr/w:ind', $paragraph)->item(0);
            $indentLeft = $paragraphIndent?->getAttribute('w:left');
            $indentHanging = $paragraphIndent?->getAttribute('w:hanging');

            $entries[] = [
                'text' => $normalizedText,
                'label' => $this->resolveListLabel($abstractNums, $numIdMap, $abstractId, $levelDefinition, $numId, $ilvl, $counterMap),
                'source_index' => $sourceIndex,
                'indent_left' => max(0, (int) ($indentLeft !== null && $indentLeft !== '' ? $indentLeft : $levelDefinition['indent_left'])),
                'indent_hanging' => max(0, (int) ($indentHanging !== null && $indentHanging !== '' ? $indentHanging : $levelDefinition['indent_hanging'])),
            ];
        }

        return $entries;
    }

    private function resolveListLabel(
        array $abstractNums,
        array $numIdMap,
        int $abstractId,
        array $levelDefinition,
        int $numId,
        int $ilvl,
        array $counterMap,
    ): string {
        $pattern = $levelDefinition['pattern'];
        $formatted = preg_replace_callback('/%\d+/', function ($matches) use ($abstractNums, $numIdMap, $abstractId, $numId, $ilvl, $counterMap) {
            $levelIndex = (int) trim($matches[0], '%') - 1;
            $targetNumId = $numId;
            $targetAbstractId = $abstractId;
            $targetDefinition = $abstractNums[$targetAbstractId][$levelIndex] ?? $abstractNums[$targetAbstractId][$ilvl] ?? null;
            if ($targetDefinition === null) {
                return '';
            }

            $key = $targetNumId . ':' . $levelIndex;
            $value = $counterMap[$key] ?? $targetDefinition['start'];
            $format = $targetDefinition['format'];

            return $this->formatListNumberToken($format, $value);
        }, $pattern);

        if ($formatted !== null && $formatted !== '') {
            return $formatted;
        }

        return $this->formatListNumberToken($levelDefinition['format'], $counterMap[$numId . ':' . $ilvl] ?? $levelDefinition['start']);
    }

    private function formatListNumberToken(string $format, int $value): string
    {
        return match ($format) {
            'lowerLetter' => $this->alphabeticHeadingLabel($value, false),
            'upperLetter' => $this->alphabeticHeadingLabel($value, true),
            'lowerRoman' => $this->romanNumberLabel($value, false),
            'upperRoman' => $this->romanNumberLabel($value, true),
            'bullet' => '•',
            'none' => '',
            default => (string) $value,
        };
    }

    private function defaultListPattern(string $format): string
    {
        return match ($format) {
            'lowerLetter' => '%1)',
            'upperLetter' => '%1.',
            'lowerRoman' => '%1.',
            'upperRoman' => '%1.',
            'bullet' => '• ',
            default => '%1.',
        };
    }

    private function stripLeadingListMarker(string $text): string
    {
        $text = preg_replace('/^\s*(?:[•·▪]|(?:[ivxlcdmIVXLCDM]+|[A-Za-z]|\d+)(?:\.\d+)*[.)])\s*/u', '', $text) ?? $text;
        if ($text === '') {
            return $text;
        }

        // Some Word documents include a leading numbering prefix without a trailing period/parenthesis,
        // so strip the exact prefix if it is already present before any heading or list number is injected.
        $text = preg_replace('/^\s*(?:[ivxlcdmIVXLCDM]+|[A-Za-z]|\d+)(?:\s*[:.])\s*/u', '', $text, 1) ?? $text;

        return $text;
    }

    private function alphabeticHeadingLabel(int $number, bool $uppercase): string
    {
        $label = '';
        $value = max(1, $number);

        while ($value > 0) {
            $value--;
            $label = chr(($uppercase ? 65 : 97) + ($value % 26)) . $label;
            $value = intdiv($value, 26);
        }

        return $label;
    }

    private function romanNumberLabel(int $value, bool $uppercase): string
    {
        $numbers = [1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD', 100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL', 10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I'];
        $result = '';
        $remaining = max(1, $value);

        foreach ($numbers as $arabic => $roman) {
            while ($remaining >= $arabic) {
                $result .= $roman;
                $remaining -= $arabic;
            }
        }

        return $uppercase ? $result : strtolower($result);
    }

    private function normalizeParagraphText(string $text): string
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[\s\x{00A0}]+/u', ' ', $text) ?? $text;

        return mb_strtolower(trim($text));
    }

    private function normalizeListMatchText(string $text): string
    {
        $text = $this->normalizeParagraphText($text);
        $text = preg_replace('/\s+([,.;:!?%)\]])/u', '$1', $text) ?? $text;
        $text = preg_replace('/([([])\s+/u', '$1', $text) ?? $text;

        return preg_replace('/\s+/u', '', trim($text)) ?? trim($text);
    }

    private function replaceInlineStyleProperty(string $style, string $property, string $value): string
    {
        $pattern = '/(?:^|;)\s*' . preg_quote($property, '/') . '\s*:[^;]*/i';
        $style = preg_replace($pattern, '', $style) ?? $style;
        $style = trim($style, " ;\t\n\r\0\x0B");

        return ($style !== '' ? $style . '; ' : '') . $property . ': ' . $value . ' !important;';
    }

    private function cacheDirectory(int $documentId, string $filePath): string
    {
        $dpi = (int) env('PDF_PAGE_DPI', 120);
        $jpegQuality = (int) env('PDF_PAGE_JPEG_QUALITY', 85);
        $hash = md5(implode('|', [
            $filePath,
            (string) (filemtime($filePath) ?: 0),
            (string) (filesize($filePath) ?: 0),
            (string) $dpi,
            (string) $jpegQuality,
            self::DOCX_RENDER_VERSION,
            'word-v3-pastel-highlights',
        ]));

        return Storage::disk('local')->path("document-previews/{$documentId}/{$hash}");
    }

    private function convertDocxToPdf(string $filePath, string $cacheDir): ?string
    {
        foreach ([
            fn () => $this->convertDocxWithMicrosoftWord($filePath, $cacheDir),
            fn () => $this->convertDocxWithLibreOffice($filePath, $cacheDir),
            fn () => $this->convertDocxWithPhpWord($filePath, $cacheDir),
        ] as $convert) {
            $pdf = $convert();
            if ($pdf) {
                return $pdf;
            }
        }

        return null;
    }

    private function convertDocxWithWordHighlights(string $filePath, string $cacheDir, array $highlights): ?string
    {
        if (! function_exists('shell_exec') || ! filter_var(env('WORD_COM_ENABLED', false), FILTER_VALIDATE_BOOL)) {
            return null;
        }

        $script = base_path('scripts/docx_to_highlighted_pdf.ps1');
        if (! is_file($script)) {
            return null;
        }

        $outputPath = $cacheDir . DIRECTORY_SEPARATOR . 'highlighted-source.pdf';
        $highlightsPath = $cacheDir . DIRECTORY_SEPARATOR . 'highlights.json';
        file_put_contents($highlightsPath, json_encode(array_map(
            fn ($highlight) => [
                'text' => $highlight->original_text,
                'color' => $highlight->color_code ?? $highlight->source?->color_code ?? '#FDE68A',
            ],
            $highlights,
        ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $command = sprintf(
            'powershell -NoProfile -ExecutionPolicy Bypass -File %s -InputPath %s -OutputPath %s -HighlightsPath %s 2>&1',
            escapeshellarg($script),
            escapeshellarg($filePath),
            escapeshellarg($outputPath),
            escapeshellarg($highlightsPath),
        );

        shell_exec($command);

        return is_file($outputPath) && filesize($outputPath) > 0 ? $outputPath : null;
    }

    private function convertDocxWithMicrosoftWord(string $filePath, string $cacheDir): ?string
    {
        if (PHP_OS_FAMILY !== 'Windows' || ! function_exists('shell_exec') || ! filter_var(env('WORD_COM_ENABLED', false), FILTER_VALIDATE_BOOL)) {
            return null;
        }

        $script = base_path('scripts/docx_to_pdf.ps1');
        if (! is_file($script)) {
            return null;
        }

        $outputPath = $cacheDir . DIRECTORY_SEPARATOR . 'converted.pdf';
        $command = sprintf(
            'powershell -NoProfile -ExecutionPolicy Bypass -File %s -InputPath %s -OutputPath %s 2>&1',
            escapeshellarg($script),
            escapeshellarg($filePath),
            escapeshellarg($outputPath),
        );
        shell_exec($command);

        return is_file($outputPath) && filesize($outputPath) > 0 ? $outputPath : null;
    }

    private function convertDocxWithLibreOffice(string $filePath, string $cacheDir): ?string
    {
        if (! function_exists('shell_exec')) {
            return null;
        }

        $binary = env('LIBREOFFICE_PATH');
        if (! is_string($binary) || $binary === '' || ! is_file($binary)) {
            return null;
        }

        $command = sprintf(
            '%s --headless --nologo --nofirststartwizard --convert-to pdf --outdir %s %s 2>&1',
            escapeshellarg($binary),
            escapeshellarg($cacheDir),
            escapeshellarg($filePath),
        );
        shell_exec($command);

        $expected = $cacheDir . DIRECTORY_SEPARATOR . pathinfo($filePath, PATHINFO_FILENAME) . '.pdf';
        return is_file($expected) && filesize($expected) > 0 ? $expected : null;
    }

    private function convertDocxWithPhpWord(string $filePath, string $cacheDir): ?string
    {
        try {
            @ini_set('memory_limit', '1024M');
            @set_time_limit(0);

            Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
            Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));

            $phpWord = IOFactory::load($filePath);
            $outputPath = $cacheDir . DIRECTORY_SEPARATOR . 'converted.pdf';
            IOFactory::createWriter($phpWord, 'PDF')->save($outputPath);

            return is_file($outputPath) && filesize($outputPath) > 0 ? $outputPath : null;
        } catch (\Throwable $e) {
            Log::warning('DOCX to PDF conversion failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

}
