<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'package_key',
        'package_credits',
        'package_expires_at',
        'pending_package_key',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'package_credits' => 'integer',
            'package_expires_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    public function hasActivePackage(): bool
    {
        return $this->package_key !== null
            && $this->package_credits > 0
            && $this->package_expires_at !== null
            && $this->package_expires_at->isFuture();
    }

    public function createdLinks(): HasMany
    {
        return $this->hasMany(Link::class, 'user_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function plagiarismChecks(): HasMany
    {
        return $this->hasMany(PlagiarismCheck::class);
    }

    public function journals(): HasMany
    {
        return $this->hasMany(Journal::class);
    }

    public function improvements(): HasMany
    {
        return $this->hasMany(Improvement::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(History::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }

    public function getSettingsOrDefaultAttribute(): UserSetting
    {
        return $this->settings ?? new UserSetting([
            'dark_mode' => false,
            'default_sources' => ['web', 'google_scholar', 'openalex', 'crossref', 'crossref_posted', 'publications'],
        ]);
    }
}
