<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Improvement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'document_id', 'plagiarism_check_id',
        'original_content', 'improved_content', 'suggestions',
        'mode', 'status', 'file_path',
        'original_similarity', 'improved_similarity'
    ];

    protected $casts = [
        'suggestions' => 'array',
        'original_similarity' => 'decimal:2',
        'improved_similarity' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function plagiarismCheck(): BelongsTo
    {
        return $this->belongsTo(PlagiarismCheck::class);
    }
}
