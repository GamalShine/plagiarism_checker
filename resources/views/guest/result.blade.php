@extends('layouts.guest_check')

@section('title', 'Hasil Pengecekan')
@section('page-title', 'Hasil Pengecekan')
@section('page-subtitle', $check->document->title)

@section('content')
@php
    $score = $check->total_similarity;
    $mainColor = '#2563eb';
    if ($score > 0 && $score <= 24) $mainColor = '#16a34a';
    elseif ($score > 24 && $score <= 49) $mainColor = '#ca8a04';
    elseif ($score > 49 && $score <= 74) $mainColor = '#ea580c';
    elseif ($score > 74) $mainColor = '#dc2626';
@endphp

<div class="mb-4 p-3 rounded-xl text-sm border border-amber-200 bg-amber-50 text-amber-800">
    Link ini sudah terpakai dan tidak bisa dibuka lagi untuk pengecekan baru. Simpan hasil di bawah jika diperlukan.
</div>

<div class="turnitin-layout" style="height: calc(100vh - 14rem);">
    <div class="turnitin-doc pc-scrollbar">
        {!! $highlightedText !!}
    </div>

    <div class="turnitin-sidebar">
        <div class="t-score-header">
            <div class="grid grid-cols-2 gap-3">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 text-center">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Plagiarized Sentences</p>
                    <div class="text-2xl sm:text-3xl font-extrabold text-red-600 dark:text-red-400">
                        {{ $check->plagiarism_percentage }}<span class="text-base">%</span>
                    </div>
                    @if($check->total_sentences > 0)
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 font-medium">({{ $check->matched_sentences }}/{{ $check->total_sentences }} kalimat)</p>
                    @endif
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 text-center">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">Overall Similarity</p>
                    <div class="text-2xl sm:text-3xl font-extrabold" style="color: {{ $mainColor }};">
                        {{ $score }}<span class="text-base">%</span>
                    </div>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 font-medium">
                        @if($score <= 24) Original
                        @elseif($score <= 49) Sedang
                        @elseif($score <= 74) Tinggi
                        @else Plagiat @endif
                    </p>
                </div>
            </div>
        </div>

        <ul class="t-source-list pc-scrollbar">
            @if($check->sources->isEmpty())
                <li class="p-8 text-center text-sm" style="color: var(--pc-text-muted);">
                    Tidak ditemukan kecocokan. Dokumen bersih.
                </li>
            @else
                @foreach($check->sources as $source)
                    <li class="t-source-item" title="{{ $source->title }}">
                        <div class="t-source-color-bar" style="background-color: {{ $source->color_code }};"></div>
                        <div class="t-source-content">
                            <div class="t-source-percent" style="color: {{ $source->color_code }};">{{ $source->similarity_score }}%</div>
                            <div class="t-source-details">
                                <div class="t-source-title">
                                    <span class="inline-block px-1.5 py-0.5 text-[10px] font-bold text-white rounded mr-1" style="background-color: {{ $source->color_code }};">{{ $source->turnitin_index }}</span>
                                    {{ $source->title }}
                                </div>
                                <div class="t-source-url">
                                    @if($source->url && $source->url !== '#')
                                        <a href="{{ $source->url }}" target="_blank" rel="noopener" class="pc-link text-xs">{{ $source->source_label }}</a>
                                    @else
                                        {{ $source->source_label }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            @endif
        </ul>
    </div>
</div>
@endsection
