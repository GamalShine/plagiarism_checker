<?php

namespace App\Services;

/**
 * TextSimilarityService
 *
 * Similarity scoring compatible with the Free-Turnitin-Plagiarism-Checker
 * baseline, with source-coverage n-gram scoring for long source documents:
 *
 *   calculateSimilarity() → Cosine similarity (semua kata, tanpa filter stop words)
 *   nGramSimilarity()     → 5-gram Jaccard similarity (matches / max(size1, size2))
 *   compareTexts()        → max(cosineSim, ngramSim)
 */
class TextSimilarityService
{
    /**
     * Cosine similarity — identik dengan calculateSimilarity() di Node.js.
     *
     * Node.js:
     *   const words1 = text1.toLowerCase().split(/\s+/);
     *   const words2 = text2.toLowerCase().split(/\s+/);
     *   // build TF vectors, dot product / (mag1 * mag2)
     */
    public function calculateSimilarity(string $text1, string $text2): float
    {
        $words1 = preg_split('/\s+/', mb_strtolower(trim($text1)), -1, PREG_SPLIT_NO_EMPTY);
        $words2 = preg_split('/\s+/', mb_strtolower(trim($text2)), -1, PREG_SPLIT_NO_EMPTY);

        if (empty($words1) || empty($words2)) {
            return 0.0;
        }

        $freq1 = array_count_values($words1);
        $freq2 = array_count_values($words2);

        $allWords = array_unique(array_merge(array_keys($freq1), array_keys($freq2)));

        $dotProduct = 0.0;
        $magnitude1 = 0.0;
        $magnitude2 = 0.0;

        foreach ($allWords as $word) {
            $val1 = $freq1[$word] ?? 0;
            $val2 = $freq2[$word] ?? 0;
            $dotProduct += $val1 * $val2;
            $magnitude1 += $val1 * $val1;
            $magnitude2 += $val2 * $val2;
        }

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($magnitude1) * sqrt($magnitude2));
    }

    /**
    * N-gram similarity measured as submitted-text coverage in the source.
     *
     * Node.js:
     *   words = text.toLowerCase().replace(/[^\w\s]/g, "").split(/\s+/)
     *   ngrams = Set of n-consecutive-word sequences
    *   return matches / ngrams1.size
     */
    public function nGramSimilarity(string $text1, string $text2, int $n = 5): float
    {
        $createNGrams = function (string $text) use ($n): array {
            // replicate: text.toLowerCase().replace(/[^\w\s]/g, "").split(/\s+/)
            $cleaned = preg_replace('/[^\p{L}\p{N}\s]/u', '', mb_strtolower($text));
            $words   = preg_split('/\s+/', trim($cleaned), -1, PREG_SPLIT_NO_EMPTY);

            $ngrams = [];
            $count  = count($words);
            for ($i = 0; $i <= $count - $n; $i++) {
                $gram = implode(' ', array_slice($words, $i, $n));
                $ngrams[$gram] = true; // use as Set (unique keys)
            }
            return $ngrams; // associative array acting as Set
        };

        $ngrams1 = $createNGrams($text1);
        $ngrams2 = $createNGrams($text2);

        if (empty($ngrams1) || empty($ngrams2)) {
            return 0.0;
        }

        // Measure source coverage of the submitted text. Dividing by the
        // entire source article made an exact sentence look unrelated when
        // the source contained thousands of additional words.
        $matches = 0;
        foreach ($ngrams1 as $gram => $_) {
            if (isset($ngrams2[$gram])) {
                $matches++;
            }
        }

        return $matches / count($ngrams1);
    }

    /**
    * compareTexts combines cosine similarity with source-coverage n-gram similarity:
     *
     *   const cosineSim = calculateSimilarity(text1, text2);
     *   const ngramSim  = nGramSimilarity(text1, text2, 5);
     *   return Math.max(cosineSim, ngramSim);
     */
    public function compareTexts(string $text1, string $text2): float
    {
        $cosineSim = $this->calculateSimilarity($text1, $text2);
        $ngramSim  = $this->nGramSimilarity($text1, $text2, 5);
        return max($cosineSim, $ngramSim);
    }

    public function prepareText(string $text, int $n = 5): array
    {
        $words = preg_split('/\s+/', mb_strtolower(trim($text)), -1, PREG_SPLIT_NO_EMPTY);
        $frequencies = array_count_values($words ?: []);
        $magnitude = 0.0;

        foreach ($frequencies as $frequency) {
            $magnitude += $frequency * $frequency;
        }

        $cleaned = preg_replace('/[^\p{L}\p{N}\s]/u', '', mb_strtolower($text));
        $ngramWords = preg_split('/\s+/', trim($cleaned), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $ngrams = [];

        for ($index = 0, $count = count($ngramWords); $index <= $count - $n; $index++) {
            $ngrams[implode(' ', array_slice($ngramWords, $index, $n))] = true;
        }

        return [
            'frequencies' => $frequencies,
            'magnitude' => $magnitude,
            'ngrams' => $ngrams,
        ];
    }

    public function comparePrepared(string $text, array $prepared): float
    {
        $sentence = $this->prepareText($text);
        $dotProduct = 0.0;
        $sentenceMagnitude = 0.0;

        foreach ($sentence['frequencies'] as $word => $frequency) {
            $dotProduct += $frequency * ($prepared['frequencies'][$word] ?? 0);
            $sentenceMagnitude += $frequency * $frequency;
        }

        $cosineSimilarity = ($sentenceMagnitude > 0 && $prepared['magnitude'] > 0)
            ? $dotProduct / (sqrt($sentenceMagnitude) * sqrt($prepared['magnitude']))
            : 0.0;

        if (empty($sentence['ngrams']) || empty($prepared['ngrams'])) {
            return $cosineSimilarity;
        }

        $matches = 0;
        foreach ($sentence['ngrams'] as $ngram => $_) {
            if (isset($prepared['ngrams'][$ngram])) {
                $matches++;
            }
        }

        $ngramSimilarity = $matches / max(count($sentence['ngrams']), count($prepared['ngrams']));

        return max($cosineSimilarity, $ngramSimilarity);
    }

    public function compareTextsPercent(string $text1, string $text2): int
    {
        return (int) round($this->compareTexts($text1, $text2) * 100);
    }

    public function bestPassageMatch(string $sentence, string $content): array
    {
        $similarity = $this->compareTexts($sentence, $content);
        return ['similarity' => $similarity, 'passage' => mb_substr($content, 0, 500)];
    }

    /**
     * Alias for backward compatibility (calculateSimilarityClean was the old stop-word-filtered version).
     * Now it just calls the plain cosine to match Node.js behavior.
     */
    public function calculateSimilarityClean(string $text1, string $text2): float
    {
        return $this->calculateSimilarity($text1, $text2);
    }
}
