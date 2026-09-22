<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Link;
use App\Services\HistoryService;
use App\Services\PlagiarismService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GuestLinkController extends Controller
{
    public function __construct(
        private PlagiarismService $plagiarismService,
        private HistoryService $historyService,
    ) {}

    public function show(string $token): View
    {
        $link = $this->findAvailableLink($token);

        $settings = $this->ownerSettings($link);

        return view('guest.link', compact('link', 'settings'));
    }

    public function check(Request $request, string $token): RedirectResponse
    {
        $link = $this->findAvailableLink($token);

        $request->validate([
            'file' => 'required|file|mimes:pdf,docx,txt|max:10240',
            'sources' => 'required|array|min:1',
            'sources.*' => 'in:web,google_scholar,elsevier,openalex,crossref,crossref_posted,publications',
        ]);

        $owner = $link->creator;
        if (!$owner) {
            abort(410, 'Link tidak valid.');
        }

        $settings = $owner->settings;
        $file = $request->file('file');

        $path = $file->store('documents', 'public');
        $originalName = $file->getClientOriginalName();
        $fullPath = Storage::disk('public')->path($path);
        $content = $this->plagiarismService->extractTextFromFile($fullPath, $file->getMimeType());

        if (empty(trim($content))) {
            Storage::disk('public')->delete($path);

            return back()->withErrors([
                'file' => 'Tidak dapat mengekstrak teks dari file. Upload dokumen asli (Word/PDF berisi teks), bukan laporan Turnitin atau scan gambar.',
            ]);
        }

        $check = DB::transaction(function () use ($link, $owner, $path, $originalName, $content, $file, $request, $settings) {
            $locked = Link::query()->whereKey($link->id)->lockForUpdate()->first();

            if (!$locked || !$locked->isAvailable()) {
                abort(410, 'Link sudah tidak tersedia.');
            }

            $document = Document::create([
                'user_id' => $owner->id,
                'title' => pathinfo($originalName, PATHINFO_FILENAME),
                'file_path' => $path,
                'original_filename' => $originalName,
                'type' => 'plagiarism',
                'status' => 'processing',
                'content' => $content,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);

            $check = $this->plagiarismService->check(
                $document,
                $request->input('sources'),
                $settings
            );

            $document->update(['status' => 'completed']);

            $locked->update([
                'is_used' => true,
                'is_active' => false,
                'used_at' => now(),
                'used_ip' => $request->ip(),
                'used_by_session_id' => $request->session()->getId(),
                'plagiarism_check_id' => $check->id,
            ]);

            $this->historyService->logPlagiarismCheck(
                $owner,
                $check->id,
                $document->title,
                $check->total_similarity,
                $request->input('sources')
            );

            return $check;
        });

        return redirect()
            ->route('guest.link.result', [$token, $check->id])
            ->with('success', 'Pengecekan plagiasi berhasil. Link ini sudah tidak bisa dipakai lagi.');
    }

    public function result(string $token, int $plagiarismCheckId): View
    {
        $link = Link::where('token', $token)->firstOrFail();

        if (!$link->plagiarism_check_id || (int) $link->plagiarism_check_id !== $plagiarismCheckId) {
            abort(404);
        }

        $check = $link->plagiarismCheck()
            ->with(['document', 'sources' => fn ($q) => $q->orderBy('similarity_score', 'desc'), 'highlights.source'])
            ->firstOrFail();

        $sourceIndexMap = [];
        $index = 1;
        foreach ($check->sources as $source) {
            $source->turnitin_index = $index;
            $sourceIndexMap[$source->id] = $index;
            $index++;
        }

        $highlightedText = app(PlagiarismController::class)
            ->buildHighlightedText($check, $sourceIndexMap);

        return view('guest.result', compact('link', 'check', 'highlightedText'));
    }

    private function findAvailableLink(string $token): Link
    {
        $link = Link::with('creator.settings')->where('token', $token)->first();

        if (!$link || !$link->isAvailable()) {
            abort(410, 'Link sudah tidak tersedia atau sudah dipakai.');
        }

        return $link;
    }

    private function ownerSettings(Link $link)
    {
        return $link->creator?->settings;
    }
}
