<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlagiarismHighlight extends Model
{
    use HasFactory;

    protected $fillable = [
        'plagiarism_check_id', 'plagiarism_source_id',
        'original_text', 'matched_text', 'color_code',
        'start_position', 'end_position', 'match_percentage'
    ];

    public function plagiarismCheck(): BelongsTo
    {
        return $this->belongsTo(PlagiarismCheck::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(PlagiarismSource::class, 'plagiarism_source_id');
    }
}
