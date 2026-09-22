<?php

namespace App\Http\Controllers;

use App\Models\History;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\PlagiarismCheck;
use App\Models\Journal;
use App\Models\Improvement;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class HistoryController extends Controller
{
    public function index(): View
    {
        $histories = History::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('history.index', compact('histories'));
    }

    public function destroyBulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:histories,id',
            'delete_backend' => 'boolean'
        ]);

        $histories = History::where('user_id', auth()->id())
            ->whereIn('id', $request->ids)
            ->get();

        foreach ($histories as $history) {
            if ($request->delete_backend) {
                // Delete associated entity based on activity_type and metadata
                if ($history->activity_type === 'plagiarism_check' && isset($history->metadata['check_id'])) {
                    $check = PlagiarismCheck::find($history->metadata['check_id']);
                    if ($check) {
                        $document = Document::find($check->document_id);
                        if ($document) {
                            if (Storage::disk('local')->exists($document->file_path)) {
                                Storage::disk('local')->delete($document->file_path);
                            }
                            $document->delete();
                        }
                        $check->delete();
                    }
                } elseif ($history->activity_type === 'journal_generate' && isset($history->metadata['journal_id'])) {
                    $journal = Journal::find($history->metadata['journal_id']);
                    if ($journal) {
                        if ($journal->file_path && Storage::disk('public')->exists($journal->file_path)) {
                            Storage::disk('public')->delete($journal->file_path);
                        }
                        $journal->delete();
                    }
                } elseif ($history->activity_type === 'improvement' && isset($history->metadata['improvement_id'])) {
                    $improvement = Improvement::find($history->metadata['improvement_id']);
                    if ($improvement) {
                        $document = Document::find($improvement->document_id);
                        if ($document) {
                            if (Storage::disk('local')->exists($document->file_path)) {
                                Storage::disk('local')->delete($document->file_path);
                            }
                            $document->delete();
                        }
                        $improvement->delete();
                    }
                }
            }
            $history->delete();
        }

        return redirect()->route('user.history.index')->with('success', 'Riwayat berhasil dihapus.');
    }
}
