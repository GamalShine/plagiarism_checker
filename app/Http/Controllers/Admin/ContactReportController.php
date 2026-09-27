<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactReport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactReportController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $channels = [
            'admin' => 'Website',
            'email' => 'Email',
            'whatsapp' => 'WhatsApp',
        ];
        $channel = (string) $request->query('channel', 'admin');
        if (! array_key_exists($channel, $channels)) {
            $channel = 'admin';
        }

        $counts = ContactReport::query()
            ->selectRaw('channel, COUNT(*) as total')
            ->groupBy('channel')
            ->pluck('total', 'channel');

        $reports = ContactReport::query()
            ->where('channel', $channel)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.index', compact('reports', 'search', 'channels', 'channel', 'counts'));
    }
}