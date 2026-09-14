<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Link;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LinkController extends Controller
{
    public function index(): View
    {
        $links = Link::with(['creator', 'plagiarismCheck.document'])
            ->latest()
            ->paginate(15);

        return view('admin.links.index', compact('links'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:150',
            'recipient_name' => 'nullable|string|max:150',
        ]);

        $title = trim((string) ($validated['title'] ?? ''));
        if ($title === '') {
            $title = 'Link ' . now()->format('d M Y H:i');
        }

        $link = Link::create([
            'user_id' => auth()->id(),
            'token' => Link::generateToken(),
            'title' => $title,
            'recipient_name' => $validated['recipient_name'] ?? null,
            'is_used' => false,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.links.index')
            ->with('success', 'Link sekali pakai berhasil dibuat.')
            ->with('new_link_url', $link->public_url);
    }

    public function update(Request $request, Link $link): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:150',
            'is_active' => 'nullable|boolean',
        ]);

        $link->update([
            'title' => $validated['title'] ?? $link->title,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.links.index')
            ->with('success', 'Link berhasil diperbarui.');
    }

    public function destroy(Link $link): RedirectResponse
    {
        $link->delete();

        return redirect()
            ->route('admin.links.index')
            ->with('success', 'Link berhasil dihapus.');
    }
}
