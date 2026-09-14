<?php

namespace App\Services;

use App\Models\Improvement;
use App\Models\PlagiarismCheck;

class ImprovementService
{
    private array $synonyms = [
        'use' => ['utilize', 'employ', 'apply', 'implement'],
        'show' => ['demonstrate', 'illustrate', 'indicate', 'reveal'],
        'find' => ['discover', 'identify', 'determine', 'establish'],
        'make' => ['create', 'produce', 'develop', 'generate'],
        'help' => ['assist', 'facilitate', 'support', 'enable'],
        'need' => ['require', 'necessitate', 'demand', 'call for'],
        'get' => ['obtain', 'acquire', 'achieve', 'gain'],
        'give' => ['provide', 'offer', 'present', 'supply'],
        'look' => ['examine', 'observe', 'investigate', 'analyze'],
        'work' => ['function', 'operate', 'perform', 'execute'],
        'study' => ['research', 'investigate', 'examine', 'analyze'],
        'important' => ['significant', 'crucial', 'essential', 'vital'],
        'many' => ['numerous', 'various', 'multiple', 'several'],
        'large' => ['substantial', 'considerable', 'extensive', 'significant'],
        'small' => ['minimal', 'limited', 'minor', 'negligible'],
        'good' => ['effective', 'efficient', 'beneficial', 'advantageous'],
        'bad' => ['detrimental', 'adverse', 'problematic', 'unfavorable'],
        'said' => ['stated', 'indicated', 'mentioned', 'expressed'],
        'also' => ['furthermore', 'additionally', 'moreover', 'likewise'],
        'but' => ['however', 'nevertheless', 'nonetheless', 'yet'],
    ];

    public function analyze(Improvement $improvement, ?PlagiarismCheck $plagiarismCheck = null): Improvement
    {
        $text = $improvement->original_content;
        $sentences = $this->extractSentences($text);
        $suggestions = [];

        $highlightedSentences = [];
        if ($plagiarismCheck) {
            $highlights = $plagiarismCheck->highlights()->with('source')->get();
            foreach ($highlights as $h) {
                $highlightedSentences[] = [
                    'text' => $h->original_text,
                    'source' => $h->source->source_label ?? 'Unknown',
                    'color' => $h->color_code,
                    'similarity' => $h->match_percentage,
                ];
            }
        }

        foreach (array_slice($sentences, 0, 30) as $i => $sentence) {
            $isMatched = $this->isMatchedSentence($sentence, $highlightedSentences);

            if ($isMatched || (strlen($sentence) > 50 && rand(0, 100) < 40)) {
                $paraphrase = $this->generateParaphrase($sentence);
                $suggestions[] = [
                    'index' => $i,
                    'original' => $sentence,
                    'suggestion' => $paraphrase,
                    'source' => $isMatched['source'] ?? 'General',
                    'color' => $isMatched['color'] ?? '#94a3b8',
                    'similarity' => $isMatched['similarity'] ?? rand(20, 60),
                    'applied' => false,
                ];
            }
        }

        $improvement->update([
            'suggestions' => $suggestions,
            'status' => 'completed',
        ]);

        return $improvement->fresh();
    }

    public function applyAll(Improvement $improvement): Improvement
    {
        $text = $improvement->original_content;
        $suggestions = $improvement->suggestions ?? [];

        foreach ($suggestions as $suggestion) {
            if (!empty($suggestion['original']) && !empty($suggestion['suggestion'])) {
                $text = str_replace($suggestion['original'], $suggestion['suggestion'], $text);
            }
        }

        $estimatedImprovedSim = max(5, ($improvement->original_similarity ?? 30) * 0.35);

        $improvement->update([
            'improved_content' => $text,
            'improved_similarity' => round($estimatedImprovedSim, 2),
            'status' => 'completed',
        ]);

        return $improvement->fresh();
    }

    public function applySelected(Improvement $improvement, array $selectedIndices): Improvement
    {
        $text = $improvement->original_content;
        $suggestions = $improvement->suggestions ?? [];

        foreach ($suggestions as $suggestion) {
            if (in_array($suggestion['index'], $selectedIndices)) {
                if (!empty($suggestion['original']) && !empty($suggestion['suggestion'])) {
                    $text = str_replace($suggestion['original'], $suggestion['suggestion'], $text);
                }
            }
        }

        $appliedCount = count($selectedIndices);
        $totalSuggestions = count($suggestions);
        $ratio = $totalSuggestions > 0 ? $appliedCount / $totalSuggestions : 0;
        $originalSim = $improvement->original_similarity ?? 30;
        $estimatedImprovedSim = max(5, $originalSim * (1 - ($ratio * 0.65)));

        $improvement->update([
            'improved_content' => $text,
            'improved_similarity' => round($estimatedImprovedSim, 2),
        ]);

        return $improvement->fresh();
    }

    private function isMatchedSentence(string $sentence, array $highlightedSentences): array|false
    {
        foreach ($highlightedSentences as $hs) {
            $needle = strtolower(substr($hs['text'], 0, 50));
            if ($needle && str_contains(strtolower($sentence), $needle)) {
                return $hs;
            }
        }
        return false;
    }

    private function generateParaphrase(string $sentence): string
    {
        // Apply synonym substitutions
        $result = $sentence;
        foreach ($this->synonyms as $word => $replacements) {
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
            if (preg_match($pattern, $result)) {
                $replacement = $replacements[array_rand($replacements)];
                $result = preg_replace($pattern, $replacement, $result, 1);
            }
        }

        // Restructure sentence if possible
        if (str_contains($result, ' because ')) {
            $parts = explode(' because ', $result, 2);
            $result = ucfirst(trim($parts[1])) . ', ' . strtolower(trim($parts[0])) . '.';
        } elseif (str_contains($result, ', which ')) {
            $result = str_replace(', which ', '. This ', $result);
        }

        // Add academic phrases at beginning
        $prefixes = [
            'Based on the analysis, ',
            'According to this perspective, ',
            'From the findings, it can be observed that ',
            'The results indicate that ',
            'Evidence suggests that ',
        ];

        if (rand(0, 1) && !str_contains(strtolower($result), 'based on') && strlen($result) > 60) {
            $prefix = $prefixes[array_rand($prefixes)];
            $result = $prefix . lcfirst(ltrim($result));
        }

        return $result;
    }

    private function extractSentences(string $text): array
    {
        $sentences = preg_split('/(?<=[.!?])\s+/', trim($text));
        return array_values(array_filter($sentences, fn($s) => strlen(trim($s)) > 20));
    }
}
