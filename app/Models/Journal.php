<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'author', 'institution', 'email',
        'abstract', 'keywords', 'content', 'template_type',
        'file_path_pdf', 'file_path_docx', 'status'
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTemplateNameAttribute(): string
    {
        $templates = [
            'scopus'   => 'Scopus Format (Internasional)',
            'sinta_1'  => 'SINTA 1 (Akreditasi Utama)',
            'sinta_2'  => 'SINTA 2 (Akreditasi Nasional)',
            'sinta_3'  => 'SINTA 3 (Akreditasi Nasional)',
            'sinta_4'  => 'SINTA 4 (Akreditasi Nasional)',
            'sinta_5'  => 'SINTA 5 (Akreditasi Nasional)',
            'sinta_6'  => 'SINTA 6 (Akreditasi Nasional)',
            'doaj'     => 'DOAJ (Open Access Global)',
            'garuda'   => 'Garuda (Ristekbrin Index)',
            'template_a' => 'Template Standar (Times New Roman)',
            'template_b' => 'Template Modern (Arial)',
        ];

        return $templates[$this->template_type] ?? strtoupper($this->template_type);
    }
}
