<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Improvement;
use App\Models\PlagiarismCheck;
use App\Services\HistoryService;
use App\Services\ImprovementService;
use App\Services\PlagiarismService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ImprovementController extends Controller
{
    public function __construct(
        private ImprovementService $improvementService,
        private PlagiarismService $plagiarismService,
        private HistoryService $historyService,
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $improvements = Improvement::where('user_id', $user->id)
            ->with(['document', 'plagiarismCheck'])
            ->latest()
            ->paginate(10);

        $completedChecks = PlagiarismCheck::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with('document')
            ->latest()
            ->take(20)
            ->get();

        return view('improvement.index', compact('improvements', 'completedChecks'));
    }

    public function analyze(Request $request): RedirectResponse
    {
        $request->validate([
            'file'               => 'nullable|file|mimes:pdf,docx,txt',
            'plagiarism_check_id' => 'nullable|exists:plagiarism_checks,id',
            'mode'               => 'required|in:automatic,manual',
        ]);

        $user = auth()->user();
        $content = '';
        $document = null;
        $plagiarismCheck = null;

        // If coming from a plagiarism check
        if ($request->plagiarism_check_id) {
            $plagiarismCheck = PlagiarismCheck::findOrFail($request->plagiarism_check_id);
            abort_if($plagiarismCheck->user_id !== $user->id, 403);
            $document = $plagiarismCheck->document;
            $content = $document->content ?? '';
        } elseif ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('documents', 'public');
            $fullPath = Storage::disk('public')->path($path);
            $content = $this->plagiarismService->extractTextFromFile($fullPath, $file->getMimeType());

            $document = Document::create([
                'user_id'           => $user->id,
                'title'             => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'file_path'         => $path,
                'original_filename' => $file->getClientOriginalName(),
                'type'              => 'improvement',
                'status'            => 'processing',
                'content'           => $content,
                'file_size'         => $file->getSize(),
                'mime_type'         => $file->getMimeType(),
            ]);
        }

        if (empty(trim($content))) {
            return back()->withErrors(['error' => 'Tidak dapat mengekstrak konten dari file.']);
        }

        $improvement = Improvement::create([
            'user_id'             => $user->id,
            'document_id'         => $document?->id,
            'plagiarism_check_id' => $plagiarismCheck?->id,
            'original_content'    => $content,
            'mode'                => $request->mode,
            'status'              => 'analyzing',
            'original_similarity' => $plagiarismCheck?->total_similarity ?? null,
        ]);

        // Analyze
        $improvement = $this->improvementService->analyze($improvement, $plagiarismCheck);

        // If automatic mode, apply all immediately
        if ($request->mode === 'automatic') {
            $improvement = $this->improvementService->applyAll($improvement);
        }

        $document?->update(['status' => 'completed']);

        return redirect()->route('user.improvement.show', $improvement->id)
            ->with('success', 'Analisis selesai! Silakan tinjau saran perbaikan.');
    }

    public function show(Improvement $improvement): View
    {
        abort_if($improvement->user_id !== auth()->id(), 403);
        return view('improvement.show', compact('improvement'));
    }

    public function apply(Request $request, Improvement $improvement): RedirectResponse
    {
        abort_if($improvement->user_id !== auth()->id(), 403);

        $request->validate([
            'mode'     => 'required|in:all,selected',
            'selected' => 'nullable|array',
            'selected.*' => 'integer',
        ]);

        if ($request->mode === 'all') {
            $improvement = $this->improvementService->applyAll($improvement);
        } else {
            $improvement = $this->improvementService->applySelected($improvement, $request->input('selected', []));
        }

        // Log history
        $docTitle = $improvement->document?->title ?? 'Dokumen';
        $this->historyService->logImprovement(
            auth()->user(),
            $improvement->id,
            $docTitle,
            (float) $improvement->original_similarity,
            (float) $improvement->improved_similarity
        );

        return redirect()->route('user.improvement.show', $improvement->id)
            ->with('success', 'Perbaikan berhasil diterapkan!');
    }

    public function download(Improvement $improvement)
    {
        abort_if($improvement->user_id !== auth()->id(), 403);

        $content = $improvement->improved_content ?? $improvement->original_content;
        $filename = 'improved_document_' . $improvement->id . '.txt';

        return response($content, 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
