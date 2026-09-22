<?php

namespace App\Services;

/**
 * TextSimilarityService
 *
 * Algoritma identik 1:1 dengan Free-Turnitin-Plagiarism-Checker/server/plagiarism.js:
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
     * N-gram similarity — identik dengan nGramSimilarity(text1, text2, n=5) di Node.js.
     *
     * Node.js:
     *   words = text.toLowerCase().replace(/[^\w\s]/g, "").split(/\s+/)
     *   ngrams = Set of n-consecutive-word sequences
     *   return matches / Math.max(ngrams1.size, ngrams2.size)
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

        // matches / Math.max(ngrams1.size, ngrams2.size)
        $matches = 0;
        foreach ($ngrams1 as $gram => $_) {
            if (isset($ngrams2[$gram])) {
                $matches++;
            }
        }

        return $matches / max(count($ngrams1), count($ngrams2));
    }

    /**
     * compareTexts — identik dengan compareTexts() di Node.js:
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
