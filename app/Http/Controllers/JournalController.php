<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Services\HistoryService;
use App\Services\JournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class JournalController extends Controller
{
    public function __construct(
        private JournalService $journalService,
        private HistoryService $historyService,
    ) {}

    public function index(): View
    {
        $journals = Journal::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('journal.index', compact('journals'));
    }

    public function create(): View
    {
        return view('journal.create');
    }

    public function generate(Request $request): RedirectResponse
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'author'        => 'required|string|max:255',
            'institution'   => 'nullable|string|max:255',
            'email'         => 'nullable|email|max:255',
            'abstract'      => 'required|string|min:100',
            'keywords'      => 'required|string|max:255',
            'template_type' => 'required|in:template_a,template_b',
            'sections'      => 'required|array|min:1',
            'sections.*.title'   => 'required|string|max:255',
            'sections.*.content' => 'required|string|min:50',
        ]);

        $journal = Journal::create([
            'user_id'       => auth()->id(),
            'title'         => $request->title,
            'author'        => $request->author,
            'institution'   => $request->institution,
            'email'         => $request->email,
            'abstract'      => $request->abstract,
            'keywords'      => $request->keywords,
            'template_type' => $request->template_type,
            'content'       => $request->sections,
            'status'        => 'draft',
        ]);

        // Generate files
        $journal = $this->journalService->generate($journal);

        // Log history
        $this->historyService->logJournalGenerate(
            auth()->user(),
            $journal->id,
            $journal->title,
            $journal->template_name
        );

        return redirect()->route('user.journal.show', $journal->id)
            ->with('success', 'Jurnal berhasil digenerate!');
    }

    public function show(Journal $journal): View
    {
        abort_if($journal->user_id !== auth()->id(), 403);
        return view('journal.show', compact('journal'));
    }

    public function download(Journal $journal, string $type = 'pdf')
    {
        abort_if($journal->user_id !== auth()->id(), 403);

        if ($type === 'docx' && $journal->file_path_docx) {
            $path = Storage::disk('public')->path($journal->file_path_docx);
            return response()->download($path, 'journal_' . $journal->id . '.docx');
        }

        if ($journal->file_path_pdf) {
            $path = Storage::disk('public')->path($journal->file_path_pdf);
            return response()->download($path, 'journal_' . $journal->id . '.pdf');
        }

        return back()->withErrors(['error' => 'File tidak tersedia.']);
    }
}
