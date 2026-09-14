<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlagiarismCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id', 'user_id', 'total_similarity',
        'sources_checked', 'chapters', 'total_sentences', 'matched_sentences',
        'status', 'error_message'
    ];

    protected $casts = [
        'sources_checked' => 'array',
        'chapters' => 'array',
        // total_similarity dihandle via Attribute accessor di bawah
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sources(): HasMany
    {
        return $this->hasMany(PlagiarismSource::class);
    }

    public function highlights(): HasMany
    {
        return $this->hasMany(PlagiarismHighlight::class);
    }

    /**
     * Accessor cerdas untuk total_similarity.
     * Jika nilai tersimpan di DB = 0 (bug lama), fallback ke:
     *   1. matched_sentences / total_sentences × 100
     *   2. Rata-rata similarity_score semua sumber (jika sumber ter-load)
     *
     * Ini memperbaiki hasil check LAMA tanpa perlu re-run check.
     */
    protected function totalSimilarity(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $raw = (float) $value;
                if ($raw > 0) {
                    return (int) round($raw);
                }

                // Fallback 1: total kata yang cocok / total kata dokumen (Turnitin standard)
                $docWords = $this->total_words;
                $matchedWords = $this->total_matched_words;
                if ($docWords > 0 && $matchedWords > 0) {
                    return (int) round(($matchedWords / $docWords) * 100);
                }

                // Fallback 2: hitung dari matched_sentences / total_sentences
                $total   = (int) $this->getRawOriginal('total_sentences');
                $matched = (int) $this->getRawOriginal('matched_sentences');
                if ($total > 0 && $matched > 0) {
                    return (int) round(($matched / $total) * 100);
                }

                return 0;
            },
            set: fn ($value) => round((float) $value, 2),
        );
    }

    public function getSimilarityColorAttribute(): string
    {
        $score = $this->total_similarity;
        if ($score >= 75) return '#ef4444'; // red
        if ($score >= 50) return '#f97316'; // orange
        if ($score >= 25) return '#eab308'; // yellow
        return '#22c55e'; // green
    }

    public function getSimilarityLabelAttribute(): string
    {
        $score = $this->total_similarity;
        if ($score >= 75) return 'Sangat Tinggi';
        if ($score >= 50) return 'Tinggi';
        if ($score >= 25) return 'Sedang';
        return 'Rendah';
    }

    public function getTotalWordsAttribute(): int
    {
        return str_word_count($this->document?->content ?? '');
    }

    public function getTotalMatchedWordsAttribute(): int
    {
        $total = 0;
        foreach ($this->sources as $source) {
            $total += $source->matched_words;
        }
        return min(max($total, 1), 1000);
    }

    public function getSourcesCountAttribute(): int
    {
        return $this->sources->count();
    }

    public function getPlagiarismPercentageAttribute(): int
    {
        $total = (int) $this->total_sentences;
        $matched = (int) $this->matched_sentences;
        if ($total > 0 && $matched > 0) {
            return (int) round(($matched / $total) * 100);
        }
        return 0;
    }

    public function getOriginalityAttribute(): float|int
    {
        $val = 100 - (float) $this->total_similarity;
        return max($val, 0);
    }
}
