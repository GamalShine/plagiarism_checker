@extends('layouts.user')

@section('title', 'Detail Jurnal')
@section('page-title', 'Detail Jurnal')
@section('page-subtitle', $journal->title)

@section('content')

    <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
        <div>
            <span class="pc-badge-neutral mb-2">{{ $journal->template_name }}</span>
            <h2 class="text-2xl font-bold tracking-tight mt-1">{{ $journal->title }}</h2>
            <p class="text-sm mt-1" style="color: var(--pc-text-muted);">{{ $journal->author }} · {{ $journal->created_at->format('d M Y') }}</p>
        </div>

        <div class="flex gap-2 w-full sm:w-auto">
            @if($journal->file_path_docx)
            <a href="{{ route('user.journal.download', ['journal' => $journal->id, 'type' => 'docx']) }}" class="pc-btn-secondary flex-1 sm:flex-none">
                Unduh DOCX
            </a>
            @endif
            @if($journal->file_path_pdf)
            <a href="{{ route('user.journal.download', ['journal' => $journal->id, 'type' => 'pdf']) }}" class="pc-btn-primary flex-1 sm:flex-none">
                Unduh PDF
            </a>
            @endif
        </div>
    </div>

    <div class="pc-card p-6 sm:p-8">
        <h3 class="pc-section-title mb-5 pb-3 border-b" style="border-color: var(--pc-border);">Preview</h3>

        <div class="w-full rounded-2xl border p-6 sm:p-10 lg:p-12 font-serif text-justify leading-relaxed" style="background: var(--pc-bg-subtle); border-color: var(--pc-border);">
            <h1 class="text-2xl font-bold text-center mb-2">{{ $journal->title }}</h1>
            <p class="text-center text-lg mb-1">{{ $journal->author }}</p>
            @if($journal->institution)
                <p class="text-center text-sm italic mb-1" style="color: var(--pc-text-muted);">{{ $journal->institution }}</p>
            @endif
            @if($journal->email)
                <p class="text-center text-sm mb-8">{{ $journal->email }}</p>
            @endif

            <div class="mb-8">
                <h4 class="font-bold text-center mb-2">ABSTRACT</h4>
                <p class="italic text-sm">{{ $journal->abstract }}</p>
                <p class="text-sm mt-3"><span class="font-bold italic">Keywords:</span> <span class="italic">{{ $journal->keywords }}</span></p>
            </div>

            <div class="space-y-6 text-sm">
                @php $sections = is_array($journal->content) ? $journal->content : json_decode($journal->content ?? '[]', true); @endphp
                @foreach($sections ?? [] as $section)
                    <div>
                        <h4 class="font-bold mb-2">{{ $section['title'] ?? '' }}</h4>
                        <p>{{ $section['content'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
