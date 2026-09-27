<?php

namespace App\Http\Controllers;

use App\Models\ContactReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HelpReportController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'category' => ['required', 'string', Rule::in([
                'Kendala teknis',
                'Bug atau error',
                'Masalah akun atau pembayaran',
                'Pertanyaan',
                'Saran lainnya',
            ])],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'channel' => ['required', Rule::in(['admin', 'email', 'whatsapp'])],
        ]);

        $report = ContactReport::create([
            ...$validated,
            'user_id' => $request->user()?->id,
        ]);

        return response()->json([
            'message' => 'Laporan berhasil disimpan.',
            'report_id' => $report->id,
        ], 201);
    }
}