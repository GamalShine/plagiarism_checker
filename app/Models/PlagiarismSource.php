<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlagiarismSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'plagiarism_check_id', 'source_name', 'source_label',
        'similarity_score', 'url', 'title', 'snippet',
        'authors', 'published_year', 'color_code'
    ];

    protected $casts = [
        'similarity_score' => 'decimal:2',
    ];

    public static array $sourceColors = [
        'web'              => '#FF6B6B',
        'wikipedia'        => '#74B9FF',
        'google_scholar'   => '#4ECDC4',
        'elsevier'         => '#FFE66D',
        'semantic_scholar' => '#A29BFE',
        'europe_pmc'       => '#55EFC4',
        'plos'             => '#FD79A8',
        'gutenberg'        => '#E17055',
        'openalex'         => '#A8E6CF',
        'crossref'         => '#FF8A5C',
        'crossref_posted'  => '#6C5CE7',
        'publications'     => '#DFE6E9',
    ];

    public static array $sourceLabels = [
        'web'              => 'Web Pages',
        'wikipedia'        => 'Wikipedia',
        'google_scholar'   => 'Google Scholar',
        'elsevier'         => 'Elsevier/Scopus',
        'semantic_scholar' => 'Semantic Scholar',
        'europe_pmc'       => 'Europe PMC',
        'plos'             => 'PLOS',
        'gutenberg'        => 'Project Gutenberg',
        'openalex'         => 'OpenAlex & arXiv',
        'crossref'         => 'Crossref Published',
        'crossref_posted'  => 'Crossref Posted',
        'publications'     => 'Publications (CORE)',
    ];

    protected function similarityScore(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => (float) $value,
            set: fn ($value) => (float) $value,
        );
    }

    public function plagiarismCheck(): BelongsTo
    {
        return $this->belongsTo(PlagiarismCheck::class);
    }

    public function highlights(): HasMany
    {
        return $this->hasMany(PlagiarismHighlight::class);
    }

    public static function getColor(string $sourceName): string
    {
        return self::$sourceColors[$sourceName] ?? '#94a3b8';
    }

    public static function getLabel(string $sourceName): string
    {
        return self::$sourceLabels[$sourceName] ?? ucfirst($sourceName);
    }

    public function getMatchedWordsAttribute(): int
    {
        $total = 0;
        foreach ($this->highlights as $h) {
            $text = trim($h->original_text ?? '');
            if ($text !== '') {
                $total += count(preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY));
            }
        }
        if ($total > 0) {
            return $total;
        }

        // Fallback jika sumber terdeteksi dari query search tapi belum memiliki potongan highlight terpisah
        $content = trim($this->plagiarismCheck?->document?->content ?? '');
        $totalWords = $content !== '' ? count(preg_split('/\s+/u', $content, -1, PREG_SPLIT_NO_EMPTY)) : 0;
        if ($totalWords <= 0) {
            return 0;
        }
        $words = (int) round(($this->similarity_score / 100) * $totalWords);
        return max(min($words, 1000), 1);
    }

    public function getTurnitinPercentageAttribute(): string
    {
        $matched = $this->matched_words;
        $totalDocWords = $this->plagiarismCheck?->total_words ?? 0;
        if ($totalDocWords <= 0 || $matched <= 0) {
            return '< 1%';
        }

        $pct = ($matched / $totalDocWords) * 100;
        if ($pct < 1.0) {
            return '< 1%';
        }

        return round($pct) . '%';
    }
}
