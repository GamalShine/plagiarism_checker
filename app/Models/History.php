<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class History extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'activity_type', 'description', 'metadata', 'icon', 'color'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static array $activityIcons = [
        'plagiarism_check'  => '🔍',
        'journal_generate'  => '📄',
        'improvement'       => '✨',
        'login'             => '🔑',
        'register'          => '👤',
    ];

    public static array $activityColors = [
        'plagiarism_check'  => '#4ECDC4',
        'journal_generate'  => '#A8E6CF',
        'improvement'       => '#FFE66D',
        'login'             => '#74b9ff',
        'register'          => '#fd79a8',
    ];
}
