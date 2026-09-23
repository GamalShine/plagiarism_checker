<?php

namespace App\Services;

use App\Models\Document;
use App\Models\History;
use App\Models\PlagiarismCheck;
use App\Models\PlagiarismHighlight;
use App\Models\PlagiarismSource;
use App\Models\UserSetting;
use App\Services\HistoryService;
use Illuminate\Support\Facades\Log;

class PlagiarismService
{
    private const OVERALL_SCORE_OFFSET = 4;
    private const MIN_CONTENT_LENGTH  = 50;   // Konten sumber minimal 50 char (sama seperti Node.js: content.length < 50)
    private const MATCH_THRESHOLD     = 0.04; // > 4% = match valid; cap display to 4% to match Turnitin-style reporting
    private const PLAGIARIZED_THRESHOLD = 50; // > 50% = plagiat

    private array $sourceColors = [
        'internet'         => '#84CC16',
        'web'              => '#84CC16',
        'wikipedia'        => '#84CC16',
        'google_scholar'   => '#84CC16',
        'elsevier'         => '#84CC16',
        'semantic_scholar' => '#84CC16',
        'europe_pmc'       => '#84CC16',
        'plos'             => '#84CC16',
        'gutenberg'        => '#84CC16',
        'publications'     => '#84CC16',
        'openalex'         => '#84CC16',
        'crossref'         => '#84CC16',
        'crossref_posted'  => '#84CC16',
        'submitted_works'  => '#84CC16',
    ];

    private array $sourceLabels = [
        'internet'         => 'Internet',
        'web'              => 'Internet',
        'wikipedia'        => 'Wikipedia',
        'google_scholar'   => 'Google Scholar',
        'elsevier'         => 'Elsevier (Scopus)',
        'semantic_scholar' => 'Semantic Scholar',
        'europe_pmc'       => 'Europe PMC',
        'plos'             => 'PLOS (Public Library of Science)',
        'gutenberg'        => 'Project Gutenberg (Books)',
        'publications'     => 'Publications',
        'openalex'         => 'Publications',
        'crossref'         => 'Crossref',
        'crossref_posted'  => 'Crossref Posted Content',
        'submitted_works'  => 'Submitted Works',
    ];

    public function __construct(
        private TextSimilarityService $similarityService,
        private SourceSearchAggregator $sourceSearch,
        private HistoryService $historyService,
    ) {}

    public function check(Document $document, array $selectedSources, ?UserSetting $settings = null, ?PlagiarismCheck $check = null): PlagiarismCheck
    {
        @set_time_limit(0); // Ditingkatkan menjadi unlimited untuk mendukung dokumen Tesis yang sangat tebal

        $check ??= PlagiarismCheck::create([
            'document_id' => $document->id,
            'user_id' => $document->user_id,
            'status' => 'processing',
            'sources_checked' => $selectedSources,
        ]);

        $check->forceFill([
            'document_id' => $document->id,
            'user_id' => $document->user_id,
            'status' => 'processing',
            'sources_checked' => $selectedSources,
        ]);
        $check->save();

        try {
            $text = $document->content ?? '';
            $sentences = $this->extractSentences($text);

            if (empty($sentences)) {
                throw new \Exception('Tidak ada kalimat valid. Pastikan dokumen memiliki kalimat panjang (>20 karakter) yang dipisah titik (.), tanda seru (!), atau tanda tanya (?)');
            }

            $selectedSentences = $this->limitSentencesForTimeBudget($sentences);

            Log::info('Plagiarism sentence budget applied', [
                'check_id' => $check->id,
                'document_sentences' => count($sentences),
                'checked_sentences' => count($selectedSentences),
            ]);

            // Per-sentence: search sumber → compare → catat hasil
            $sentenceResults = [];
            $allMatches     = [];
            $similaritySum  = 0.0;

            foreach ($selectedSentences as $sentence) {
                $result = $this->checkSentence($sentence, $selectedSources, $settings);

                $sentenceResults[] = $result;
                $similaritySum    += $result['similarity'];

                foreach ($result['allMatches'] as $match) {
                    $allMatches[] = $match;
                }
            }

            // Sesuaikan skor overall dengan nilai koreksi aplikasi setelah pembulatan.
            // overallScore = Math.round(sum(sentence.similarity) / totalSentences)
            // plagiarismPercentage = Math.round((plagiarizedCount / totalSentences) * 100)
            $total = count($selectedSentences);

            $plagiarizedCount = count(array_filter(
                $sentenceResults,
                fn($r) => $r['isPlagiarized']
            ));

            $overallScore = $total > 0
                ? max(0, (int) round($similaritySum / $total) - self::OVERALL_SCORE_OFFSET)
                : 0;

            $plagiarismPct = $total > 0
                ? (int) round(($plagiarizedCount / $total) * 100)
                : 0;

            Log::info('Plagiarism score computed (Node.js Parity)', [
                'check_id'         => $check->id,
                'total_sentences'  => $total,
                'plagiarized'      => $plagiarizedCount,
                'overallScore'     => $overallScore,
                'plagiarismPct'    => $plagiarismPct,
            ]);

            $this->persistResults($check, $sentenceResults, $allMatches, $overallScore, $total, $plagiarizedCount, $text);

            if (! History::where('user_id', $document->user_id)
                ->where('activity_type', 'plagiarism_check')
                ->whereJsonContains('metadata->check_id', $check->id)
                ->exists()) {
                $this->historyService->logPlagiarismCheck(
                    $document->user,
                    $check->id,
                    $document->title,
                    $overallScore,
                    $selectedSources,
                );
            }

        } catch (\Exception $e) {
            Log::error('Plagiarism check failed', ['error' => $e->getMessage(), 'check_id' => $check->id]);
            $check->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        return $check->fresh(['sources', 'highlights']);
    }

    /**
     * Cek satu kalimat: search sumber → resolve konten → compare.
     * Alur ini meniru cara kerja Node.js: per-sentence, bukan massal.
     */
    private function checkSentence(string $sentence, array $selectedSources, ?UserSetting $settings): array
    {
        // 1. Query semua sumber dengan kalimat ini sebagai query
        $sourceHits = $this->sourceSearch->searchAll($sentence, $selectedSources, $settings);

        // 2. Resolve konten setiap sumber (batasi web fetch agar tidak lambat)
        $sourceContents = [];

        foreach ($sourceHits as $source) {
            $content = $this->sourceSearch->resolveContent($source, true);

            if (mb_strlen($content) < self::MIN_CONTENT_LENGTH) {
                continue;
            }

            $sourceContents[] = ['source' => $source, 'content' => $content];
        }

        // 3. Bandingkan kalimat ke setiap sumber
        return $this->compareSentenceLocally($sentence, $sourceContents);
    }

    private function compareSentenceLocally(string $sentence, array $sourceContents): ?array
    {
        $matchedSources = [];

        foreach ($sourceContents as $item) {
            $source = $item['source'];
            $content = $item['content'];

            $similarity = $this->similarityService->compareTexts($sentence, $content);

            if ($similarity > self::MATCH_THRESHOLD) {
                $matchedSources[] = [
                    'url' => $source['url'] ?? '#',
                    'title' => $source['title'] ?? 'Unknown Source',
                    'similarity' => (int) round($similarity * 100),
                    'repository' => $source['repository'],
                    'repositoryLabel' => $source['repositoryLabel'] ?? $this->sourceLabels[$source['repository']] ?? $source['repository'],
                    'content' => mb_substr($content, 0, 500),
                    'sentence' => $sentence,
                ];
            }
        }

        if (empty($matchedSources)) {
            return [
                'sentence' => $sentence,
                'similarity' => 0,
                'isPlagiarized' => false,
                'matches' => [],
                'allMatches' => [],
                'bestMatch' => null,
            ];
        }

        usort($matchedSources, function ($a, $b) {
            if ($b['similarity'] !== $a['similarity']) {
                return $b['similarity'] <=> $a['similarity'];
            }

            $priorityA = SourceSearchAggregator::REPOSITORY_PRIORITY[$a['repository']] ?? 99;
            $priorityB = SourceSearchAggregator::REPOSITORY_PRIORITY[$b['repository']] ?? 99;

            return $priorityA <=> $priorityB;
        });

        $maxSimilarity = $matchedSources[0]['similarity'];

        return [
            'sentence' => $sentence,
            'similarity' => $maxSimilarity,
            'isPlagiarized' => $maxSimilarity > self::PLAGIARIZED_THRESHOLD,
            'matches' => array_slice($matchedSources, 0, 5),
            'allMatches' => $matchedSources,
            'bestMatch' => $matchedSources[0],
        ];
    }

    private function persistResults(
        PlagiarismCheck $check,
        array $sentenceResults,
        array $allMatches,
        int $overallScore,
        int $totalSentences,
        int $plagiarizedCount,
        string $documentContent = ''
    ): void {
        $sourceStats = [];

        foreach ($allMatches as $match) {
            $key = $this->sourceKey($match);
            if (!isset($sourceStats[$key])) {
                $sourceStats[$key] = [
                    'matches' => 0,
                    'topSimilarity' => 0,
                    'bestMatch' => $match,
                ];
            }
            $sourceStats[$key]['matches']++;
            if ($match['similarity'] > $sourceStats[$key]['topSimilarity']) {
                $sourceStats[$key]['topSimilarity'] = $match['similarity'];
                $sourceStats[$key]['bestMatch'] = $match;
            }
        }

        uasort($sourceStats, fn($a, $b) => $b['topSimilarity'] <=> $a['topSimilarity']);

        $savedSources = [];

        foreach ($sourceStats as $key => $stats) {
            $best = $stats['bestMatch'];
            $repo = $best['repository'];
            $color = $this->sourceColors[$repo] ?? '#E2E8F0';
            $label = $best['repositoryLabel'] ?? $this->sourceLabels[$repo] ?? ucfirst($repo);

            $savedSources[$key] = PlagiarismSource::create([
                'plagiarism_check_id' => $check->id,
                'source_name' => $repo,
                'source_label' => $label,
                'similarity_score' => $stats['topSimilarity'],
                'url' => $best['url'] ?? '#',
                'title' => $best['title'] ?? 'Unknown',
                'snippet' => mb_substr($best['content'] ?? '', 0, 300),
                'color_code' => $color,
            ]);
        }

        $highlightedSourceIds = [];
        $createdHighlightKeys = [];

        foreach ($allMatches as $match) {
            $sourceKey = $this->sourceKey($match);
            $sourceModel = $savedSources[$sourceKey] ?? null;
            if (!$sourceModel) {
                continue;
            }

            $sentence = trim((string) ($match['sentence'] ?? ''));
            if ($sentence === '') {
                continue;
            }

            foreach ($this->findSentencePositions($documentContent, $sentence) as $positions) {
                $highlightKey = $sourceModel->id . '|' . ($positions['start'] ?? 0);
                if (isset($createdHighlightKeys[$highlightKey])) {
                    continue;
                }

                PlagiarismHighlight::create([
                    'plagiarism_check_id' => $check->id,
                    'plagiarism_source_id' => $sourceModel->id,
                    'original_text' => $sentence,
                    'matched_text' => mb_substr($match['content'] ?? $match['title'] ?? '', 0, 500),
                    'color_code' => $sourceModel->color_code,
                    'match_percentage' => (int) ($match['similarity'] ?? 0),
                    'start_position' => $positions['start'] ?? 0,
                    'end_position' => $positions['end'] ?? 0,
                ]);
                $highlightedSourceIds[$sourceModel->id] = true;
                $createdHighlightKeys[$highlightKey] = true;
            }
        }

        // Some sources are aggregated from search results without a sentence-level match.
        // Add a highlight only when the source snippet/title is actually present in the document.
        foreach ($savedSources as $key => $sourceModel) {
            if (isset($highlightedSourceIds[$sourceModel->id])) {
                continue;
            }

            $bestMatch = $sourceStats[$key]['bestMatch'] ?? [];
            $candidates = [
                trim((string) ($bestMatch['content'] ?? '')),
                trim((string) ($bestMatch['title'] ?? '')),
            ];

            foreach ($candidates as $candidate) {
                if (mb_strlen($candidate) < 10) {
                    continue;
                }

                $candidate = mb_substr($candidate, 0, 500);
                $start = mb_stripos($documentContent, $candidate);
                if ($start === false) {
                    continue;
                }

                PlagiarismHighlight::create([
                    'plagiarism_check_id' => $check->id,
                    'plagiarism_source_id' => $sourceModel->id,
                    'original_text' => $candidate,
                    'matched_text' => $candidate,
                    'color_code' => $sourceModel->color_code,
                    'match_percentage' => (int) $sourceModel->similarity_score,
                    'start_position' => $start,
                    'end_position' => $start + mb_strlen($candidate),
                ]);
                break;
            }
        }

        $check->update([
            'total_similarity' => $overallScore,
            'total_sentences' => $totalSentences,
            'matched_sentences' => $plagiarizedCount,
            'status' => 'completed',
        ]);
    }

    private function sourceKey(array $match): string
    {
        $url = trim($match['url'] ?? '');
        if ($url !== '' && $url !== '#') {
            return md5($url);
        }

        return md5(($match['repository'] ?? 'unknown') . '|' . ($match['title'] ?? 'unknown'));
    }

    private function findSentencePosition(string $content, string $sentence): ?array
    {
        return $this->findSentencePositions($content, $sentence)[0] ?? null;
    }

    private function findSentencePositions(string $content, string $sentence): array
    {
        $sentence = trim($sentence);
        if ($sentence === '' || $content === '') {
            return [];
        }

        $positions = [];
        $offset = 0;
        $sentenceLength = mb_strlen($sentence);

        while (($start = mb_stripos($content, $sentence, $offset)) !== false) {
            $positions[] = [
                'start' => $start,
                'end' => $start + $sentenceLength,
            ];
            $offset = $start + max(1, $sentenceLength);
        }

        // Word/PDF extraction may replace spaces with line breaks or tabs.
        if ($positions !== []) {
            return $positions;
        }

        $pattern = preg_quote($sentence, '/');
        $pattern = preg_replace('/\\\\s+/u', '\\s+', $pattern);
        if (is_string($pattern) && preg_match('/' . $pattern . '/iu', $content, $match, PREG_OFFSET_CAPTURE)) {
            $matchedText = (string) ($match[0][0] ?? '');
            $byteOffset = (int) ($match[0][1] ?? 0);
            $start = mb_strlen(substr($content, 0, $byteOffset));

            return [[
                'start' => $start,
                'end' => $start + mb_strlen($matchedText),
            ]];
        }

        return [];
    }

    private function extractSentences(string $text): array
    {
        $parts = preg_split('/[.!?]+/u', trim($text));
        $sentences = array_values(array_filter(
            array_map('trim', $parts),
            fn($s) => mb_strlen($s) > 20
        ));

        return $sentences;
    }

    private function limitSentencesForTimeBudget(array $sentences): array
    {
        $limit = (int) env('PLAGIARISM_MAX_SENTENCES', 50);

        if ($limit <= 0 || count($sentences) <= $limit) {
            return $sentences;
        }

        $sampled = [];
        $total = count($sentences);

        for ($index = 0; $index < $limit; $index++) {
            $sourceIndex = $limit === 1
                ? 0
                : (int) round($index * ($total - 1) / ($limit - 1));
            $sampled[] = $sentences[$sourceIndex];
        }

        return $sampled;
    }

    private function isValidSentence(string $sentence): bool
    {
        $sentence = trim($sentence);
        if (mb_strlen($sentence) <= 20) {
            return false;
        }

        preg_match_all('/\p{L}/u', $sentence, $letters);

        return count($letters[0] ?? []) >= 15;
    }

    public function normalizeSelectedChapterKeys(array $chapters): array
    {
        $normalized = [];

        foreach ($chapters as $chapter) {
            $value = trim((string) $chapter);
            if ($value === '') {
                continue;
            }

            $upper = strtoupper($value);
            $key = null;

            if (str_starts_with($upper, 'BAB')) {
                preg_match('/BAB\s+([IVXLCDM0-9]+)/i', $upper, $match);
                if (! empty($match[1])) {
                    $key = (string) $this->romanToArabic($match[1]);
                }
            } elseif (preg_match('/^\d+$/', $value)) {
                $key = (string) (int) $value;
            } else {
                $key = strtolower(str_replace([' ', '-'], '_', $upper));
            }

            if ($key !== null && ! in_array($key, $normalized, true)) {
                $normalized[] = $key;
            }
        }

        return $normalized;
    }

    public function extractTextFromFile(string $filePath, string $mimeType, array $chapters = []): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $text = '';

        if (in_array($extension, ['txt', 'text'], true)) {
            $text = file_get_contents($filePath) ?: '';
        } elseif ($extension === 'docx') {
            $text = $this->extractFromDocx($filePath);
        } elseif ($extension === 'pdf') {
            $text = $this->extractFromPdf($filePath);
        } else {
            $text = file_get_contents($filePath) ?: '';
        }

        if (!empty($chapters)) {
            $text = $this->filterByChapters($text, $chapters);
        }

        return $text;
    }

    private function filterByChapters(string $text, array $chapters): string
    {
        // Use standard capturing group ( ) instead of (?: ) so PREG_SPLIT_DELIM_CAPTURE returns the delimiter
        $pattern = '/\b(BAB\s+[IVXLCDM0-9]+|ABSTRAK|KATA PENGANTAR|DAFTAR ISI|DAFTAR PUSTAKA|LAMPIRAN)\b/ui';
        $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        if (count($parts) <= 1) {
            return ''; // Tidak ada Heading/BAB yang terdeteksi
        }

        $filteredText = '';

        for ($i = 1; $i < count($parts); $i += 2) {
            $headingRaw = trim($parts[$i]);
            $headingUpper = strtoupper($headingRaw);
            $content = $parts[$i+1] ?? '';

            $matchKey = null;
            if (str_starts_with($headingUpper, 'BAB')) {
                preg_match('/BAB\s+([IVXLCDM0-9]+)/i', $headingUpper, $m);
                if (!empty($m[1])) {
                    $matchKey = (string)$this->romanToArabic($m[1]);
                }
            } else {
                $matchKey = strtolower(str_replace(' ', '_', $headingUpper));
            }

            if ($matchKey && (in_array($matchKey, $chapters, true) || in_array(strtolower($headingUpper), $chapters, true))) {
                $filteredText .= "\n" . $headingUpper . "\n" . $content;
            }
        }

        return $filteredText;
    }

    private function romanToArabic(string $roman): int
    {
        $roman = strtoupper($roman);
        if (is_numeric($roman)) {
            return (int) $roman;
        }

        $romans = [
            'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90,
            'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1,
        ];

        $result = 0;
        foreach ($romans as $key => $value) {
            while (strpos($roman, $key) === 0) {
                $result += $value;
                $roman = substr($roman, strlen($key));
            }
        }

        return $result;
    }

    private function extractFromDocx(string $filePath): string
    {
        try {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                $content = '';
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if (str_contains($name, 'word/document.xml')) {
                        $xml = $zip->getFromIndex($i);
                        $content = strip_tags(str_replace('</w:p>', "\n\n", $xml));
                        break;
                    }
                }
                $zip->close();
                return trim($content);
            }
        } catch (\Exception $e) {
            // fall through
        }

        return '';
    }

    private function extractFromPdf(string $filePath): string
    {
        $text = $this->extractPdfWithPdftotext($filePath);

        if ($this->isUsableExtractedText($text, $filePath)) {
            return $this->normalizeExtractedText($text);
        }

        $text = $this->extractPdfWithSmalot($filePath);

        if ($this->isUsableExtractedText($text, $filePath)) {
            return $this->normalizeExtractedText($text);
        }

        Log::warning('PDF text extraction unusable', [
            'file' => $filePath,
            'size' => @filesize($filePath),
            'sample_length' => mb_strlen(trim($text)),
        ]);

        return '';
    }

    private function extractPdfWithPdftotext(string $filePath): string
    {
        $binary = $this->findPdftotextBinary();
        if (!$binary) {
            return '';
        }

        $command = sprintf(
            '%s -layout %s -',
            escapeshellarg($binary),
            escapeshellarg($filePath)
        );

        $output = shell_exec($command);

        return is_string($output) ? $output : '';
    }

    private function findPdftotextBinary(): ?string
    {
        $candidates = array_filter([
            env('PDFTOTEXT_PATH'),
            'C:\\laragon\\bin\\git\\mingw64\\bin\\pdftotext.exe',
            'pdftotext',
        ]);

        foreach ($candidates as $candidate) {
            if ($candidate === 'pdftotext') {
                return $candidate;
            }

            if (is_string($candidate) && file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function extractPdfWithSmalot(string $filePath): string
    {
        if (@filesize($filePath) > 8 * 1024 * 1024) {
            return '';
        }

        try {
            $previousLimit = ini_get('memory_limit');
            @ini_set('memory_limit', '1024M');

            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();

            if ($previousLimit !== false) {
                @ini_set('memory_limit', $previousLimit);
            }

            return is_string($text) ? $text : '';
        } catch (\Exception $e) {
            Log::warning('Smalot PDF extraction failed: ' . $e->getMessage(), ['file' => $filePath]);

            return '';
        }
    }

    private function isUsableExtractedText(string $text, string $filePath): bool
    {
        $text = trim($text);
        if (mb_strlen($text) < 200) {
            return false;
        }

        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        if (! is_array($words) || count($words) < 50) {
            return false;
        }

        $alphaWords = array_filter($words, fn($word) => is_string($word) && preg_match('/\p{L}{3,}/u', $word) === 1);
        if (count($alphaWords) / max(count($words), 1) < 0.3) {
            return false;
        }

        return true;
    }

    private function normalizeExtractedText(string $text): string
    {
        $text = preg_replace("/\r\n|\r/", "\n", $text);
        $text = preg_replace('/[^\S\n]+/u', ' ', $text);
        $text = preg_replace('/\n{3,}/u', "\n\n", $text);

        return trim($text);
    }
}
