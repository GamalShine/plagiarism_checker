<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Link extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'title',
        'description',
        'recipient_name',
        'recipient_email',
        'expires_at',
        'is_used',
        'is_active',
        'used_at',
        'used_ip',
        'used_by_session_id',
        'plagiarism_check_id',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'is_active' => 'boolean',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public static function generateToken(): string
    {
        return Str::random(48);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function plagiarismCheck(): BelongsTo
    {
        return $this->belongsTo(PlagiarismCheck::class);
    }

    public function isAvailable(): bool
    {
        if ($this->is_used || $this->used_at !== null) {
            return false;
        }

        if (array_key_exists('is_active', $this->getAttributes()) && !$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function getPublicUrlAttribute(): string
    {
        return route('guest.link.show', $this->token);
    }

    public function getLabelAttribute(): ?string
    {
        return $this->title;
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_used || $this->used_at) {
            return 'Terpakai';
        }

        if (array_key_exists('is_active', $this->getAttributes()) && !$this->is_active) {
            return 'Nonaktif';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'Kedaluwarsa';
        }

        return 'Tersedia';
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->is_used || $this->used_at) {
            return 'warning';
        }

        if ((array_key_exists('is_active', $this->getAttributes()) && !$this->is_active)
            || ($this->expires_at && $this->expires_at->isPast())) {
            return 'neutral';
        }

        return 'success';
    }
}
