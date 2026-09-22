<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Services\HistoryService;
use App\Services\JournalService;
use App\Services\PlagiarismService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class JournalController extends Controller
{
    public function __construct(
        private JournalService $journalService,
        private HistoryService $historyService,
        private PlagiarismService $plagiarismService,
    ) {}

    public function index(): View
    {
        $journals = Journal::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        $layout = str_starts_with(request()->route()?->getName() ?? '', 'admin.') ? 'layouts.admin' : 'layouts.user';

        return view('journal.index', array_merge(compact('journals'), ['layout' => $layout]));
    }

    public function create(): View
    {
        $layout = str_starts_with(request()->route()?->getName() ?? '', 'admin.') ? 'layouts.admin' : 'layouts.user';

        return view('journal.create', ['layout' => $layout]);
    }

    public function generate(Request $request): RedirectResponse
    {
        // Mode 1: Auto-generate dari Upload File Dokumen (Word/PDF)
        if ($request->hasFile('file') || $request->input('mode') === 'upload') {
            $request->validate([
                'file'          => 'required|file|mimes:pdf,docx,txt|max:307200',
                'template_type' => 'required|string|max:50',
                'title'         => 'nullable|string|max:255',
                'author'        => 'nullable|string|max:255',
                'institution'   => 'nullable|string|max:255',
            ], [
                'file.required' => 'Pilih file dokumen (PDF/DOCX) naskah Anda.',
                'file.mimes'    => 'Format file harus berupa PDF, DOCX, atau TXT.',
                'file.max'      => 'Ukuran file maksimal adalah 300MB.',
            ]);

            $file = $request->file('file');
            $path = $file->store('documents', 'public');
            $fullPath = Storage::disk('public')->path($path);
            $extractedText = $this->plagiarismService->extractTextFromFile($fullPath, $file->getMimeType());

            if (empty(trim($extractedText))) {
                return back()->withErrors(['file' => 'Tidak dapat mengekstrak teks dari file tersebut. Pastikan dokumen berisi teks asli.']);
            }

            $parsedData = $this->parseDocumentStructure($extractedText, $file->getClientOriginalName(), $request);

            $journal = Journal::create([
                'user_id'       => auth()->id(),
                'title'         => $request->input('title') ?: $parsedData['title'],
                'author'        => $request->input('author') ?: ($parsedData['author'] ?: auth()->user()->name),
                'institution'   => $request->input('institution') ?: ($parsedData['institution'] ?: 'Fakultas / Institusi Akademik'),
                'email'         => $request->input('email') ?: auth()->user()->email,
                'abstract'      => $parsedData['abstract'],
                'keywords'      => $parsedData['keywords'],
                'template_type' => $request->input('template_type', 'scopus'),
                'content'       => $parsedData['sections'],
                'status'        => 'draft',
            ]);

            $journal = $this->journalService->generate($journal);

            $this->historyService->logJournalGenerate(
                auth()->user(),
                $journal->id,
                $journal->title,
                $journal->template_name
            );

            $routePrefix = str_starts_with(request()->route()?->getName() ?? '', 'admin.') ? 'admin' : 'user';

            return redirect()->route($routePrefix . '.journal.show', $journal->id)
                ->with('success', 'Jurnal akademik berhasil digenerate otomatis berdasarkan template ' . $journal->template_name . '!');
        }

        // Mode 2: Form Manual
        $request->validate([
            'title'         => 'required|string|max:255',
            'author'        => 'required|string|max:255',
            'institution'   => 'nullable|string|max:255',
            'email'         => 'nullable|email|max:255',
            'abstract'      => 'required|string|min:50',
            'keywords'      => 'required|string|max:255',
            'template_type' => 'required|string|max:50',
            'sections'      => 'required|array|min:1',
            'sections.*.title'   => 'required|string|max:255',
            'sections.*.content' => 'required|string|min:20',
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

        $routePrefix = str_starts_with(request()->route()?->getName() ?? '', 'admin.') ? 'admin' : 'user';

        return redirect()->route($routePrefix . '.journal.show', $journal->id)
            ->with('success', 'Jurnal berhasil digenerate!');
    }

    /**
     * Memecah struktur dokumen naskah (Abstrak, BAB I s/d Daftar Pustaka) secara otomatis
     */
    private function parseDocumentStructure(string $text, string $originalFilename, Request $request): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($text));
        $firstFewLines = array_filter(array_slice($lines, 0, 10), fn($l) => !empty(trim($l)));

        // Title candidate
        $title = pathinfo($originalFilename, PATHINFO_FILENAME);
        if (!empty($firstFewLines)) {
            $candidateTitle = trim(reset($firstFewLines));
            if (mb_strlen($candidateTitle) > 10 && mb_strlen($candidateTitle) < 200) {
                $title = $candidateTitle;
            }
        }

        // Abstract candidate
        $abstract = '';
        if (preg_match('/(?:ABSTRAK|ABSTRACT)\s*[:\-\.]?\s*(.*?)(?=\n\s*(?:KATA KUNCI|KEYWORDS|\bBAB\b|1\.\s+PENDAHULUAN|INTRODUCTION)|$)/uis', $text, $m)) {
            $abstract = trim($m[1]);
        }
        if (empty($abstract)) {
            $abstract = 'Penelitian ini menganalisis struktur dan substansi naskah ilmiah untuk memenuhi standar publikasi jurnal terakreditasi.';
        }

        // Keywords candidate
        $keywords = '';
        if (preg_match('/(?:KATA KUNCI|KEYWORDS)\s*[:\-\.]?\s*([^\n\r]+)/ui', $text, $km)) {
            $keywords = trim($km[1]);
        }
        if (empty($keywords)) {
            $keywords = 'publikasi, naskah akademik, analisis, metodologi';
        }

        // Split Sections berdasarkan BAB / Heading standar
        $pattern = '/\b(BAB\s+[IVXLCDM0-9]+(?:\s*[:\.\-]\s*[^\n]+)?|PENDAHULUAN|INTRODUCTION|METODOLOGI|METODE PENELITIAN|METHODOLOGY|HASIL DAN PEMBAHASAN|RESULTS AND DISCUSSION|KESIMPULAN|CONCLUSION|DAFTAR PUSTAKA|REFERENCES)\b/ui';
        $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        $sections = [];
        if (count($parts) > 1) {
            for ($i = 1; $i < count($parts); $i += 2) {
                $secTitle = trim($parts[$i]);
                $secBody = trim($parts[$i + 1] ?? '');
                if (mb_strlen($secBody) > 20) {
                    $sections[] = [
                        'title' => Str::limit($secTitle, 60, ''),
                        'content' => Str::limit($secBody, 4000),
                    ];
                }
            }
        }

        // Jika tidak ada heading terdeteksi, bagi naskah menjadi bagian terstruktur
        if (empty($sections)) {
            $paragraphs = array_values(array_filter(preg_split('/\n\s*\n/', $text), fn($p) => mb_strlen(trim($p)) > 30));
            $totalP = count($paragraphs);
            if ($totalP >= 3) {
                $sections[] = ['title' => 'Pendahuluan', 'content' => $paragraphs[0] . "\n\n" . ($paragraphs[1] ?? '')];
                $midContent = implode("\n\n", array_slice($paragraphs, 2, max(1, $totalP - 3)));
                $sections[] = ['title' => 'Metodologi & Pembahasan', 'content' => Str::limit($midContent, 4000)];
                $sections[] = ['title' => 'Kesimpulan', 'content' => end($paragraphs)];
            } else {
                $sections[] = ['title' => 'Uraian Naskah Utama', 'content' => Str::limit($text, 4000)];
            }
        }

        return [
            'title'       => $title,
            'author'      => auth()->user()->name,
            'institution' => 'Fakultas / Lembaga Penelitian',
            'abstract'    => Str::limit($abstract, 1000),
            'keywords'    => Str::limit($keywords, 150),
            'sections'    => $sections,
        ];
    }

    public function show(Journal $journal): View
    {
        abort_if($journal->user_id !== auth()->id(), 403);

        $layout = str_starts_with(request()->route()?->getName() ?? '', 'admin.') ? 'layouts.admin' : 'layouts.user';

        return view('journal.show', array_merge(compact('journal'), ['layout' => $layout]));
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
