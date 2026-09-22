@extends($layout ?? 'layouts.user')
@section('title', 'Hasil Pengecekan Plagiasi')
@section('page-title', 'Hasil Pengecekan')
@section('page-subtitle', $check->document->title)
@section('header-actions')
<a href="{{ route($routePrefix.'.plagiarism.export', $check->id) }}" class="pc-btn-secondary pc-btn-sm">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
    </svg>
    Export PDF
</a>
@if($check->total_similarity > 0)
<form action="{{ route('user.improvement.analyze') }}" method="POST" class="inline">
    @csrf
    <input type="hidden" name="plagiarism_check_id" value="{{ $check->id }}">
    <input type="hidden" name="mode" value="manual">
    <button type="submit" class="pc-btn-primary pc-btn-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
        Perbaiki
    </button>
</form>
@endif
@endsection
@section('page-class', '')
@section('content')
@php
$score = $check->total_similarity;
$mainColor = '#2563eb';
if ($score > 0 && $score <= 24) $mainColor='#16a34a' ;
    elseif ($score> 24 && $score <= 49) $mainColor='#ca8a04' ;
        elseif ($score> 49 && $score <= 74) $mainColor='#ea580c' ;
            elseif ($score> 74) $mainColor = '#dc2626';

            $sourceIndexMap = $sourceIndexMap ?? [];
            if (empty($sourceIndexMap)) {
            $idx = 1;
            foreach ($check->sources as $s) {
            $sourceIndexMap[$s->id] = $idx++;
            }
            }

            $highlightsList = [];
            foreach ($check->highlights as $h) {
            $raw = trim($h->original_text);
            if (mb_strlen($raw) < 5) continue;
                $lines=preg_split('/\r\n|\r|\n/', $raw);
                foreach ($lines as $line) {
                $trimmed=trim($line);
                if (mb_strlen($trimmed)>= 5) {
                $highlightsList[] = [
                'original_text' => $trimmed,
                'color' => $h->source->color_code ?? '#ff0000',
                'index' => $sourceIndexMap[$h->plagiarism_source_id] ?? '*',
                'source_label' => $h->source->source_label ?? '',
                'match_percentage' => $h->match_percentage
                ];
                }
                }
                }

                $_hj = json_encode(array_values($highlightsList), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                $_fn = json_encode($check->document->original_filename ?? $check->document->file_path, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                @endphp

                <div class="turnitin-layout">
                    <div class="turnitin-doc pc-scrollbar">
                        <div id="doc-body" class="pc-card px-6 py-8 sm:px-10 sm:py-10 bg-white dark:!bg-slate-800 max-w-4xl mx-auto shadow-lg leading-relaxed text-slate-800 dark:text-slate-100 text-[14px]">{!! $highlightedText !!}</div>
                    </div>
                    <div class="turnitin-sidebar">
                        <div class="t-score-header">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 text-center">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Kalimat Terdeteksi</p>
                                    <div class="text-2xl sm:text-3xl font-extrabold text-red-600 dark:text-red-400">{{ count($check->highlights) }}</div>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 text-center">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Similarity</p>
                                    <div class="text-2xl sm:text-3xl font-extrabold" style="color: {{ $mainColor }};">{{ number_format($score, 1) }}<span class="text-base font-bold">%</span></div>
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 font-medium">
                                        @if($score <= 24) Original
                                            @elseif($score <=49) Sedang
                                            @elseif($score <=74) Tinggi
                                            @else Plagiat
                                            @endif
                                            </p>
                                </div>
                            </div>
                        </div>
                        <div class="t-section">
                            <h3 class="t-section-title">Sumber</h3>
                            <div class="space-y-2 max-h-64 overflow-y-auto pc-scrollbar pr-1">
                                @forelse($check->sources as $src)
                                @php($srcIndex = $sourceIndexMap[$src->id] ?? '*')
                                @php($srcHC = $check->highlights->where('plagiarism_source_id', $src->id)->count())
                                <div class="t-source-item flex items-start gap-2 p-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                    <span class="t-source-index" style="background-color: {{ $src->color_code ?? '#ff0000' }};">{{ $srcIndex }}</span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200 truncate" title="{{ $src->source_label }}">{{ $src->source_label }}</p>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400">{{ $srcHC }} kalimat cocok</p>
                                    </div>
                                </div>
                                @empty
                                <p class="text-xs text-slate-500 italic">Tidak ada sumber</p>
                                @endforelse
                            </div>
                        </div>
                        @if(count($check->highlights) > 0)
                        <div class="t-section">
                            <h3 class="t-section-title">Frase Terdeteksi</h3>
                            <div class="space-y-2 overflow-y-auto pc-scrollbar pr-1 flex-1 min-h-0">
                                @foreach($check->highlights as $h)
                                @php($tIndex = $sourceIndexMap[$h->plagiarism_source_id] ?? '*')
                                @php($color = $h->source->color_code ?? '#ff0000')
                                <div class="t-highlight-item p-2 rounded-lg border border-slate-200 dark:border-slate-700">
                                    <div class="flex items-center gap-1 mb-1">
                                        <span class="t-source-index text-[9px]" style="background-color: {{ $color }};">{{ $tIndex }}</span>
                                        <span class="text-[10px] font-semibold text-slate-500">{{ $h->match_percentage }}%</span>
                                    </div>
                                    <p class="text-xs text-slate-700 dark:text-slate-300 italic line-clamp-3" title="{{ $h->original_text }}">"{{ Str::limit($h->original_text, 120) }}"</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endsection
                @push('styles')
                <style>
                    mark.t-highlight {
                        padding: 1px 2px;
                        border-radius: 3px;
                    }

                    #doc-body p,
                    #doc-body div {
                        margin-bottom: 0.75rem;
                    }
                </style>
                @endpush
                @push('scripts')
                <script src="https://cdnjs.cloudflare.com/ajax/libs/mark.js/8.11.1/mark.min.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var H = {!! $_hj !!};
                        var t = document.getElementById('doc-body');
                        if (!t || !window.Mark) return;
                        try {
                            var mk = new Mark(t);
                            H.forEach(function(h) {
                                if (!h.original_text) return;
                                mk.mark(h.original_text, {
                                    element: 'mark',
                                    className: 't-highlight',
                                    exclude: ['script', 'style', 'sup'],
                                    separateWordSearch: false,
                                    acrossElements: true,
                                    ignoreJoiners: true,
                                    caseSensitive: false,
                                    each: function(el) {
                                        if (el.querySelectorAll('sup.t-badge').length > 0) return;
                                        el.style.backgroundColor = (h.color || '#ff0000') + '66';
                                        var b = document.createElement('sup');
                                        b.className = 't-badge';
                                        b.style.backgroundColor = h.color || '#ff0000';
                                        b.textContent = h.index || '*';
                                        b.title = (h.source_label || '') + ' (' + h.match_percentage + '%)';
                                        el.insertBefore(b, el.firstChild);
                                    },
                                    filter: function() {
                                        var p = arguments[0].parentNode;
                                        while (p) {
                                            if (p.nodeType === 1 && p.tagName === 'MARK' && p.classList && p.classList.contains('t-highlight')) return false;
                                            p = p.parentNode;
                                        }
                                        return true;
                                    }
                                });
                            });
                        } catch (e) {}
                    });
                </script>
                @endpush