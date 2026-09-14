<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'dark_mode', 'email_notifications', 'default_sources',
        'serpapi_key', 'elsevier_api_key', 'elsevier_enabled',
        'google_cse_key', 'google_cse_id'
    ];

    protected $casts = [
        'dark_mode' => 'boolean',
        'email_notifications' => 'boolean',
        'elsevier_enabled' => 'boolean',
        'default_sources' => 'array',
    ];

    protected $hidden = ['serpapi_key', 'elsevier_api_key', 'google_cse_key'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDefaultSourcesOrAllAttribute(): array
    {
        return $this->default_sources ?? [
            'web', 'google_scholar', 'openalex', 'crossref', 'crossref_posted', 'publications'
        ];
    }
}
