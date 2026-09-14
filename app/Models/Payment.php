<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'guest_token',
        'snap_token',
        'temp_file_path',
        'original_filename',
        'sources',
        'amount',
        'package_key',
        'package_name',
        'package_quota',
        'package_days',
        'currency',
        'status',
        'payment_type',
        'paid_at',
        'midtrans_payload',
        'plagiarism_check_id',
        'chapters',
    ];

    protected $casts = [
        'sources'          => 'array',
        'midtrans_payload' => 'array',
        'paid_at'          => 'datetime',
        'package_quota'    => 'integer',
        'package_days'     => 'integer',
        'chapters'         => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plagiarismCheck(): BelongsTo
    {
        return $this->belongsTo(PlagiarismCheck::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
