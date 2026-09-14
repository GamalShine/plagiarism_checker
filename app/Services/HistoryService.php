<?php

namespace App\Services;

use App\Models\History;
use App\Models\User;

class HistoryService
{
    public function log(
        User|int $user,
        string $activityType,
        string $description,
        array $metadata = [],
        ?string $icon = null,
        ?string $color = null
    ): History {
        $userId = $user instanceof User ? $user->id : $user;

        $defaultIcons = [
            'plagiarism_check' => '🔍',
            'journal_generate' => '📄',
            'improvement'      => '✨',
            'login'            => '🔑',
            'register'         => '👤',
            'export'           => '📥',
            'settings'         => '⚙️',
        ];

        $defaultColors = [
            'plagiarism_check' => '#4ECDC4',
            'journal_generate' => '#A8E6CF',
            'improvement'      => '#FFE66D',
            'login'            => '#74b9ff',
            'register'         => '#fd79a8',
            'export'           => '#b2bec3',
            'settings'         => '#636e72',
        ];

        return History::create([
            'user_id'       => $userId,
            'activity_type' => $activityType,
            'description'   => $description,
            'metadata'      => $metadata,
            'icon'          => $icon ?? ($defaultIcons[$activityType] ?? '📌'),
            'color'         => $color ?? ($defaultColors[$activityType] ?? '#94a3b8'),
        ]);
    }

    public function logPlagiarismCheck(User $user, int $checkId, string $docTitle, float $similarity, array $sources): History
    {
        return $this->log(
            $user,
            'plagiarism_check',
            "Cek plagiarisme: \"{$docTitle}\" - Similarity: {$similarity}%",
            [
                'check_id' => $checkId,
                'doc_title' => $docTitle,
                'similarity' => $similarity,
                'sources' => $sources,
            ]
        );
    }

    public function logJournalGenerate(User $user, int $journalId, string $title, string $template): History
    {
        return $this->log(
            $user,
            'journal_generate',
            "Generate jurnal: \"{$title}\" dengan {$template}",
            [
                'journal_id' => $journalId,
                'title' => $title,
                'template' => $template,
            ]
        );
    }

    public function logImprovement(User $user, int $improvementId, string $docTitle, float $originalSim, float $improvedSim): History
    {
        return $this->log(
            $user,
            'improvement',
            "Perbaikan file: \"{$docTitle}\" - {$originalSim}% → {$improvedSim}%",
            [
                'improvement_id' => $improvementId,
                'doc_title' => $docTitle,
                'original_similarity' => $originalSim,
                'improved_similarity' => $improvedSim,
            ]
        );
    }
}
