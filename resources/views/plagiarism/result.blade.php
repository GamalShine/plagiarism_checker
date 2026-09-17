@if(!($embedded ?? false))
@extends($layout ?? 'layouts.user')
@endif
@section('title', 'Hasil Pengecekan Plagiarisme')
@section('page-title', 'Hasil Pengecekan')
@section('page-subtitle', $check->document->title)
@section('page-class', '')
@if(!($embedded ?? false))
@section('content')
@endif
@php
$score = $check->total_similarity;
$mainColor = '#2563eb';
if ($score > 0 && $score <= 24) $mainColor='#16a34a' ; elseif ($score> 24 && $score <= 49) $mainColor='#ca8a04' ; elseif
        ($score> 49 && $score <= 74) $mainColor='#ea580c' ; elseif ($score> 74) $mainColor = '#dc2626';

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
            if (mb_strlen($raw) < 5) continue; $highlightsList[]=[ 'original_text'=> $raw,
                'color' => $h->source->color_code ?? '#ff0000',
                'index' => $sourceIndexMap[$h->plagiarism_source_id] ?? '*',
                'source_label' => $h->source->source_label ?? '',
                'source_id' => $h->plagiarism_source_id,
                'match_percentage' => $h->match_percentage
                ];
                }
                @endphp

                @if($check->status === 'processing')
                <div id="check-processing"
                    class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-800">
                    <div class="h-1.5 overflow-hidden bg-slate-100 dark:bg-slate-700">
                        <div
                            class="processing-bar h-full w-1/3 bg-gradient-to-r from-indigo-500 via-sky-500 to-emerald-500">
                        </div>
                    </div>
                    <div class="p-6 sm:p-8">
                        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p
                                    class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-600 dark:text-indigo-400">
                                    Live analysis</p>
                                <h2 class="mt-2 text-2xl font-black text-slate-900 dark:text-white">Menganalisis dokumen
                                </h2>
                                <p id="processing-stage-text" class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                    Menyiapkan dokumen untuk dipindai...</p>
                            </div>
                            <div
                                class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full border-8 border-indigo-100 dark:border-indigo-950/60">
                                <svg class="h-9 w-9 animate-spin text-indigo-600 dark:text-indigo-400" fill="none"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <circle class="opacity-20" cx="12" cy="12" r="9" stroke="currentColor"
                                        stroke-width="2.5" />
                                    <path stroke="currentColor" stroke-linecap="round" stroke-width="2.5"
                                        d="M21 12a9 9 0 0 1-9 9" />
                                </svg>
                            </div>
                        </div>
                        <div class="mt-8 grid gap-3 sm:grid-cols-4">
                            @foreach(['Membaca dokumen', 'Mencari sumber', 'Mencocokkan teks', 'Menyusun laporan'] as $step => $label)
                            <div
                                class="processing-step flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-3 text-xs font-semibold text-slate-500 dark:border-slate-700 dark:text-slate-400">
                                <span
                                    class="step-icon flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[11px] dark:bg-slate-700">{{ $step + 1 }}</span><span>{{ $label }}</span>
                            </div>
                            @endforeach
                        </div>
                        <div
                            class="mt-6 flex items-center justify-between gap-4 text-xs text-slate-400 dark:text-slate-500">
                            <span>Hasil akan muncul otomatis setelah semua sumber selesai dianalisis.</span><span
                                id="processing-elapsed" class="shrink-0 font-semibold">00:00</span>
                        </div>
                    </div>
                </div>
                <script>
                (function() {
                    const statusUrl = @json(route($routePrefix . '.plagiarism.status', $check->id));
                    const startedAt = Date.now();
                    const stageText = document.getElementById('processing-stage-text');
                    const elapsedText = document.getElementById('processing-elapsed');
                    const steps = document.querySelectorAll('.processing-step');
                    const messages = ['Membaca dan mengekstrak isi dokumen...',
                        'Mencari referensi dari sumber yang dipilih...',
                        'Membandingkan kalimat dengan sumber ditemukan...',
                        'Menyusun highlight dan laporan akhir...'
                    ];

                    function updateProgress() {
                        const elapsed = Math.floor((Date.now() - startedAt) / 1000);
                        const activeStep = Math.min(Math.floor(elapsed / 12), steps.length - 1);
                        if (elapsedText) elapsedText.textContent =
                            `${String(Math.floor(elapsed / 60)).padStart(2, '0')}:${String(elapsed % 60).padStart(2, '0')}`;
                        if (stageText) stageText.textContent = messages[activeStep];
                        steps.forEach((step, index) => {
                            step.classList.toggle('is-done', index < activeStep);
                            step.classList.toggle('is-active', index === activeStep);
                            if (index < activeStep) step.querySelector('.step-icon').textContent = 'OK';
                        });
                    }
                    updateProgress();
                    const progressTimer = setInterval(updateProgress, 1000);
                    const statusTimer = setInterval(async function() {
                        try {
                            const response = await fetch(statusUrl, {
                                headers: {
                                    Accept: 'application/json'
                                }
                            });
                            if (!response.ok) return;
                            const data = await response.json();
                            if (data.status === 'completed' || data.status === 'failed') {
                                clearInterval(progressTimer);
                                clearInterval(statusTimer);
                                window.location.reload();
                            }
                        } catch (error) {
                            console.warn('Status pengecekan belum dapat dimuat:', error);
                        }
                    }, 3000);
                })();
                </script>
                @elseif($check->status === 'failed')
                <div
                    class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-200">
                    <strong>Pengecekan gagal.</strong>
                    {{ $check->error_message ?: 'Terjadi kesalahan saat memproses dokumen.' }}
                </div>
                @endif

                @if($check->status === 'completed')
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start pb-8">
                    {{-- BAGIAN DOKUMEN (KIRI / TENGAH) --}}
                    <div id="doc-container" class="mobile-document-scroll lg:col-span-8 flex flex-col gap-3.5 min-h-0">
                        {{-- Header Status Bar Dokumen & Mode Switcher --}}
                        <div
                            class="flex items-center justify-between px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 shadow-sm text-xs shrink-0">
                            <div class="flex items-center gap-2 min-w-0">
                                <span
                                    class="inline-flex items-center justify-center p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </span>
                                <span class="font-semibold text-slate-700 dark:text-slate-200 truncate"
                                    title="{{ $check->document->original_filename }}">
                                    {{ $check->document->original_filename }}
                                </span>
                            </div>

                            <div class="flex items-center gap-3">
                                <div
                                    class="flex items-center bg-slate-100 dark:bg-slate-700 p-0.5 rounded-lg text-[11px] font-semibold">
                                    <button type="button" id="tab-word"
                                        class="px-2.5 py-1 rounded-md text-slate-600 dark:text-slate-300 hover:text-indigo-600 transition-all">Dokumen
                                        Word Asli</button>
                                    <button type="button" id="tab-plain"
                                        class="px-2.5 py-1 rounded-md bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-2xs transition-all">Teks
                                        Ekstrak</button>
                                </div>
                                <div
                                    class="hidden sm:flex items-center gap-2 text-slate-500 dark:text-slate-400 font-medium border-l border-slate-200 dark:border-slate-700 pl-3">
                                    <span>{{ number_format($check->total_words) }} kata</span>
                                </div>
                            </div>
                        </div>

                        {{-- Lembar Kertas Dokumen Modern --}}
                        <div
                            class="relative rounded-2xl bg-slate-100/70 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/80 shadow-xl overflow-hidden flex-1 flex flex-col min-h-0">
                            {{-- Aksen atas kertas --}}
                            <div
                                class="h-1.5 w-full bg-gradient-to-r from-indigo-500 via-sky-500 to-emerald-500 shrink-0">
                            </div>

                            {{-- VIEW 1: DOCX PREVIEW RENDERING 1:1 --}}
                            <div id="docx-container" style="display: none;"
                                class="p-4 sm:p-6 overflow-y-auto flex-1 min-h-0 pc-scrollbar flex flex-col items-center">
                                <div id="docx-loading"
                                    class="flex flex-col items-center justify-center py-16 text-slate-500 gap-3">
                                    <svg class="animate-spin w-8 h-8 text-indigo-600" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                    </svg>
                                    <span class="text-xs font-semibold">Memuat layout Word 1:1 beserta stabilo...</span>
                                </div>
                                <div id="docx-render-target" class="w-full flex flex-col items-center"></div>
                            </div>

                            {{-- VIEW 2: TEKS EKSTRAK BERSIH --}}
                            <div id="doc-body"
                                class="p-8 sm:p-12 text-slate-800 dark:text-slate-100 text-[15px] leading-[2.1] font-normal tracking-wide selection:bg-indigo-100 selection:text-indigo-900 dark:selection:bg-indigo-900 dark:selection:text-indigo-100 overflow-y-auto flex-1 min-h-0 pc-scrollbar bg-white dark:bg-slate-800"
                                style="display: block;">
                                {!! $highlightedText !!}
                            </div>
                        </div>
                    </div>

                    {{-- BAGIAN SIDEBAR HASIL & METRIK (KANAN) --}}
                    <div id="sidebar-panel" class="lg:col-span-4 flex flex-col gap-4">
                        @if(!($publicMode ?? false))
                        <div class="grid grid-cols-2 gap-3.5">
                            <a href="{{ route($routePrefix.'.plagiarism.export', $check->id) }}"
                                class="pc-btn-secondary pc-btn-sm w-full justify-center shadow-sm inline-flex items-center gap-1.5 font-medium transition-all">
                                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>Export PDF</span>
                            </a>
                            @if($check->total_similarity > 0)
                            <form action="{{ route($routePrefix.'.improvement.analyze') }}" method="POST"
                                class="w-full">
                                @csrf
                                <input type="hidden" name="plagiarism_check_id" value="{{ $check->id }}">
                                <input type="hidden" name="mode" value="manual">
                                <button type="submit"
                                    class="pc-btn-primary pc-btn-sm w-full justify-center shadow-sm inline-flex items-center gap-1.5 font-medium transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    <span>Perbaiki Teks</span>
                                </button>
                            </form>
                            @endif
                        </div>
                        @endif

                        {{-- KARTU SKOR UTAMA (SIMILARITY & KALIMAT) --}}
                        <div
                            class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 p-4 sm:p-5 shadow-lg relative overflow-hidden shrink-0">
                            <div
                                class="absolute -right-8 -top-8 w-28 h-28 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none">
                            </div>

                            <div class="grid grid-cols-2 gap-3.5 relative z-10">
                                {{-- Kotak Similarity --}}
                                <div class="p-3.5 rounded-xl bg-slate-50/80 dark:bg-slate-800/80 border border-slate-100 dark:border-slate-700/70 text-center transition-all hover:shadow-sm relative group"
                                    x-data="{ showEditModal: false }">
                                    <div class="flex flex-col items-center justify-center">
                                        <span
                                            class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-400 block mb-1">Overall
                                            Similarity</span>
                                        <div class="text-3xl sm:text-4xl font-black tracking-tight flex items-baseline justify-center"
                                            style="color: {{ $mainColor }};" id="similarity-value">
                                            {{ (int) round($score) }}<span class="text-lg font-bold ml-0.5">%</span>
                                        </div>
                                        <div class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                            style="background-color: {{ $mainColor }}15; color: {{ $mainColor }};"
                                            id="similarity-badge">
                                            <span class="w-1.5 h-1.5 rounded-full"
                                                style="background-color: {{ $mainColor }};"></span>
                                            <span id="similarity-label">@if($score <= 24) Original @elseif($score <=49)
                                                    Sedang @elseif($score <=74) Tinggi @else Plagiat @endif</span>
                                        </div>
                                    </div>

                                    {{-- Edit Button Positioned Top-Right --}}
                                    @if(auth()->check() && auth()->user()->isAdmin())
                                    <button @click="showEditModal = true" type="button"
                                        class="absolute top-2 right-2 p-2 rounded-lg bg-indigo-500/0 hover:bg-indigo-500/20 text-indigo-500 hover:text-indigo-600 dark:hover:bg-indigo-500/30 transition-all duration-200 opacity-50 hover:opacity-100 group-hover:opacity-100"
                                        title="Edit Overall Similarity">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    {{-- Edit Modal dengan Backdrop Blur --}}
                                    <div x-show="showEditModal" x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                        x-transition:leave="transition ease-in duration-200"
                                        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                        class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 sm:p-6"
                                        @click.self="showEditModal = false" style="display: none;" x-cloak>
                                        <div x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-200"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-6 sm:p-8 max-w-sm w-full border border-slate-200/50 dark:border-slate-700/50 relative overflow-hidden">

                                            {{-- Gradient Background --}}
                                            <div
                                                class="absolute -top-20 -right-20 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none">
                                            </div>
                                            <div
                                                class="absolute -bottom-20 -left-20 w-40 h-40 bg-blue-500/10 rounded-full blur-3xl pointer-events-none">
                                            </div>

                                            {{-- Content --}}
                                            <div class="relative z-10">
                                                <div class="flex items-center justify-between mb-6">
                                                    <div class="flex items-center gap-3">
                                                        <div
                                                            class="p-2.5 rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600 text-white">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <h3
                                                                class="text-lg font-bold text-slate-900 dark:text-white">
                                                                Edit Similarity</h3>
                                                            <p class="text-xs text-slate-500 dark:text-slate-400">Ubah
                                                                nilai overall similarity</p>
                                                        </div>
                                                    </div>
                                                    <button @click="showEditModal = false" type="button"
                                                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors p-1">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>

                                                <form @submit.prevent="submitSimilarityUpdate"
                                                    id="similarity-edit-form">
                                                    <div class="mb-6">
                                                        <label for="similarity-input"
                                                            class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-3">Nilai
                                                            Similarity</label>
                                                        <div class="relative">
                                                            <input type="number" id="similarity-input" min="0" max="100"
                                                                step="0.01" value="{{ (int) round($score) }}"
                                                                class="w-full px-4 pr-10 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700/50 text-slate-900 dark:text-slate-100 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all text-center text-lg font-bold"
                                                                style="appearance: textfield; -moz-appearance: textfield;"
                                                                required autocomplete="off">
                                                            <span
                                                                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-slate-500 dark:text-slate-400 text-lg font-bold pointer-events-none">%</span>
                                                        </div>
                                                        <div class="mt-3 flex gap-2 text-xs">
                                                            <div
                                                                class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-green-50 dark:bg-green-950/30 text-green-700 dark:text-green-400 font-medium">
                                                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                                                0-24%: Original
                                                            </div>
                                                            <div
                                                                class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-orange-50 dark:bg-orange-950/30 text-orange-700 dark:text-orange-400 font-medium">
                                                                <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                                                                75%+: Plagiat
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center gap-3">
                                                        <button type="submit"
                                                            class="flex-1 px-4 py-3 bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white rounded-xl font-bold transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                            Simpan
                                                        </button>
                                                        <button type="button" @click="showEditModal = false"
                                                            class="flex-1 px-4 py-3 border-2 border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-xl font-bold transition-colors duration-200">
                                                            Batal
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                {{-- Kotak Kalimat Terdeteksi --}}
                                <div
                                    class="p-3.5 rounded-xl bg-slate-50/80 dark:bg-slate-800/80 border border-slate-100 dark:border-slate-700/70 text-center transition-all hover:shadow-sm">
                                    <span
                                        class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-400 block mb-1">Kalimat
                                        Terdeteksi</span>
                                    <div
                                        class="text-3xl sm:text-4xl font-black tracking-tight text-slate-800 dark:text-slate-100 flex items-baseline justify-center">
                                        {{ count($check->highlights) }}
                                    </div>
                                    <div
                                        class="mt-2 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30">
                                        <span>{{ $check->sources->count() }} Sumber</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- DAFTAR SUMBER (PRIMARY SOURCES) --}}
                        @php
                        $visibleSources = $check->sources->filter(fn($s) => $s->matched_words < 1000 && $s->
                            matched_words > 0);
                            @endphp
                            <div
                                class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 p-4 sm:p-5 shadow-lg shrink-0">
                                <div
                                    class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-700/60">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-4 rounded-full bg-indigo-600 dark:bg-indigo-500"></span>
                                        <h3
                                            class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                                            Primary Sources</h3>
                                    </div>
                                    <span
                                        class="text-[11px] font-medium text-slate-400 dark:text-slate-500">{{ $visibleSources->count() }}
                                        total</span>
                                </div>

                                <div class="space-y-2 max-h-60 overflow-y-auto pc-scrollbar pr-1">
                                    @forelse($visibleSources as $src)
                                    @php($srcIndex = $sourceIndexMap[$src->id] ?? '*')
                                    @php($srcHC = $check->highlights->where('plagiarism_source_id', $src->id)->count())
                                    <div class="group flex items-start gap-3 p-2 rounded-xl border border-slate-200/70 dark:border-slate-700/70 hover:border-indigo-300 dark:hover:border-indigo-600 hover:bg-indigo-50/40 dark:hover:bg-slate-700/50 transition-all cursor-pointer shadow-2xs"
                                        data-source-id="{{ $src->id }}" data-source-index="{{ $srcIndex }}"
                                        data-source-color="{{ $src->color_code ?? '#ff0000' }}">
                                        <span
                                            class="inline-flex items-center justify-center w-5 h-5 rounded-md text-white font-bold text-[10px] shadow-xs shrink-0 mt-0.5 transition-transform group-hover:scale-105"
                                            style="background-color: {{ $src->color_code ?? '#ff0000' }};">
                                            {{ $srcIndex }}
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors"
                                                title="{{ $src->title ?: $src->source_label }}">
                                                {{ $src->title ?: $src->source_label }}
                                            </p>
                                            <div
                                                class="flex items-center gap-2 mt-0.5 text-[10px] text-slate-400 dark:text-slate-500 font-medium">
                                                <span>{{ $src->source_label }}</span>
                                                <span>&bull;</span>
                                                <span
                                                    class="text-slate-600 dark:text-slate-300 font-semibold">{{ $src->matched_words }}
                                                    words</span>
                                                <span>&bull;</span>
                                                <span
                                                    class="font-bold text-indigo-600 dark:text-indigo-400">{{ $src->turnitin_percentage }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="py-4 text-center text-xs text-slate-400 italic">Tidak ditemukan sumber
                                        kemiripan</div>
                                    @endforelse
                                </div>
                            </div>

                            {{-- DAFTAR FRASE TERDETEKSI --}}
                            @if(count($check->highlights) > 0)
                            <div
                                class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 p-4 sm:p-5 shadow-lg shrink-0">
                                <div
                                    class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100 dark:border-slate-700/60">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-4 rounded-full bg-rose-500"></span>
                                        <h3
                                            class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                                            Frase Terdeteksi</h3>
                                    </div>
                                    <span
                                        class="text-[11px] font-medium text-slate-400 dark:text-slate-500">{{ count($check->highlights) }}
                                        frase</span>
                                </div>

                                <div class="space-y-2 overflow-y-auto pc-scrollbar pr-1 max-h-60">
                                    @foreach($check->highlights as $h)
                                    @php($tIndex = $sourceIndexMap[$h->plagiarism_source_id] ?? '*')
                                    @php($color = $h->source->color_code ?? '#ff0000')
                                    <div class="group p-2.5 rounded-xl border border-slate-200/70 dark:border-slate-700/70 hover:border-slate-300 dark:hover:border-slate-600 hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition-all cursor-pointer shadow-2xs"
                                        data-highlight-source-id="{{ $h->plagiarism_source_id }}"
                                        data-highlight-text="{{ e($h->original_text) }}"
                                        data-highlight-color="{{ $color }}">
                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <div class="flex items-center gap-1.5">
                                                <span
                                                    class="inline-flex items-center justify-center w-4 h-4 rounded text-white font-bold text-[9px] shadow-2xs"
                                                    style="background-color: {{ $color }};">
                                                    {{ $tIndex }}
                                                </span>
                                                <span
                                                    class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 truncate max-w-[130px]">
                                                    {{ $h->source->source_label ?? 'Sumber' }}
                                                </span>
                                            </div>
                                            <span
                                                class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 dark:bg-slate-700/70 text-slate-600 dark:text-slate-300">
                                                {{ $h->match_percentage }}% match
                                            </span>
                                        </div>
                                        <p class="text-[11px] leading-relaxed text-slate-600 dark:text-slate-300 italic line-clamp-2 pl-1 border-l-2 border-slate-200 dark:border-slate-700 group-hover:border-indigo-400 transition-colors"
                                            title="{{ $h->original_text }}">
                                            "{{ Str::limit($h->original_text, 130) }}"
                                        </p>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                    </div>
                </div>

                <style>
                #similarity-input::-webkit-outer-spin-button,
                #similarity-input::-webkit-inner-spin-button {
                    -webkit-appearance: none;
                    margin: 0;
                }
                </style>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const form = document.getElementById('similarity-edit-form');
                    if (!form) return;

                    function showNotification(message, type = 'success') {
                        Swal.fire({
                            icon: type === 'success' ? 'success' : 'error',
                            title: type === 'success' ? 'Berhasil!' : 'Gagal!',
                            text: message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer);
                                toast.addEventListener('mouseleave', Swal.resumeTimer);
                            },
                            customClass: {
                                container: 'z-[9999]',
                                popup: 'shadow-lg backdrop-blur-sm',
                                title: 'text-sm font-bold',
                                htmlContainer: 'text-sm'
                            }
                        });
                    }

                    form.addEventListener('submit', async function(e) {
                        e.preventDefault();

                        const input = document.getElementById('similarity-input');
                        const newValue = parseFloat(input.value);

                        if (newValue < 0 || newValue > 100) {
                            showNotification('Nilai harus antara 0 dan 100', 'error');
                            return;
                        }

                        try {
                            const response = await fetch(
                                '{{ route("admin.plagiarism.update_similarity", $check->id) }}', {
                                    method: 'PATCH',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]')?.content || '',
                                    },
                                    body: JSON.stringify({
                                        total_similarity: newValue
                                    })
                                });

                            if (!response.ok) {
                                const error = await response.json();
                                showNotification('Error: ' + (error.message || 'Gagal menyimpan'),
                                    'error');
                                return;
                            }

                            const data = await response.json();

                            // Update the display
                            const valueEl = document.getElementById('similarity-value');
                            const labelEl = document.getElementById('similarity-label');
                            const badgeEl = document.getElementById('similarity-badge');

                            if (valueEl && labelEl && badgeEl) {
                                valueEl.innerHTML = Math.round(data.total_similarity) +
                                    '<span class="text-lg font-bold ml-0.5">%</span>';

                                // Update label text
                                const score = data.total_similarity;
                                let labelText = 'Original';
                                if (score > 24 && score <= 49) labelText = 'Sedang';
                                else if (score > 49 && score <= 74) labelText = 'Tinggi';
                                else if (score > 74) labelText = 'Plagiat';

                                labelEl.textContent = labelText;

                                // Update badge background color
                                badgeEl.style.backgroundColor = data.similarity_color + '15';
                                badgeEl.style.color = data.similarity_color;
                                badgeEl.querySelector('span.w-1\\.5').style.backgroundColor = data
                                    .similarity_color;
                            }

                            // Close the modal using Alpine
                            const alpineComponent = document.querySelector(
                                '[x-data*="showEditModal"]');
                            if (alpineComponent && alpineComponent.__x) {
                                alpineComponent.__x.$data.showEditModal = false;
                            }

                            showNotification('✨ Overall similarity berhasil diperbarui!',
                                'success');
                        } catch (error) {
                            console.error('Error:', error);
                            showNotification('Terjadi kesalahan: ' + error.message, 'error');
                        }
                    });
                });
                </script>
                @endif
                @if(!($embedded ?? false))
                @endsection
                @endif

                @push('styles')
                <style>
                @keyframes processing-bar {
                    0% {
                        transform: translateX(-100%);
                    }

                    60%,
                    100% {
                        transform: translateX(300%);
                    }
                }

                .processing-bar {
                    animation: processing-bar 2.4s ease-in-out infinite;
                }

                .processing-step.is-active {
                    border-color: rgb(99 102 241 / 0.45);
                    background: rgb(238 242 255 / 0.75);
                    color: rgb(67 56 202);
                }

                .processing-step.is-active .step-icon {
                    background: rgb(79 70 229);
                    color: white;
                }

                .processing-step.is-done {
                    border-color: rgb(16 185 129 / 0.35);
                    color: rgb(5 150 105);
                }

                .processing-step.is-done .step-icon {
                    background: rgb(16 185 129);
                    color: white;
                }

                @media (max-width: 1023px) {
                    .mobile-document-scroll {
                        height: calc(100vh - 140px) !important;
                        max-height: calc(100vh - 140px) !important;
                        overflow: hidden !important;
                    }
                }

                /* Paksa seluruh container dan konten render Word dan Teks menggunakan font Times New Roman */
                #docx-render-target,
                #docx-render-target *,
                .docx-wrapper,
                .docx-wrapper *,
                .docx-wrapper>section.docx,
                .docx-wrapper>section.docx *,
                #doc-body,
                #doc-body * {
                    font-family: 'Times New Roman', Times, 'Liberation Serif', serif !important;
                }

                .docx-wrapper {
                    background: transparent !important;
                    padding: 0 !important;
                    display: flex !important;
                    flex-direction: column !important;
                    align-items: center !important;
                    width: 100% !important;
                }

                .docx-wrapper>section.docx {
                    background: #ffffff !important;
                    color: #1a1a1a !important;
                    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08) !important;
                    margin-bottom: 2.5rem !important;
                    border-radius: 2px !important;
                    border: 1px solid #d1d5db !important;
                    box-sizing: border-box !important;
                    max-width: 100% !important;
                    page-break-after: always !important;
                    break-after: page !important;
                    position: relative !important;
                    line-height: 2.0 !important;
                    font-size: 16px !important;
                }

                /* Style Foto / Gambar di dokumen Word agar 1:1 dan persis seperti di Word aslinya */
                .docx-wrapper img,
                #docx-render-target img,
                #doc-body img {
                    max-width: 100% !important;
                    height: auto !important;
                    display: block !important;
                    margin: 1.5rem auto !important;
                    object-fit: contain !important;
                    border-radius: 0px !important;
                    border: 1px solid rgba(0, 0, 0, 0.1) !important;
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
                }

                #doc-body {
                    font-family: 'Times New Roman', Times, 'Liberation Serif', serif !important;
                    font-size: 16px !important;
                    line-height: 2.0 !important;
                    text-align: justify !important;
                    text-justify: inter-word !important;
                    background-color: #ffffff !important;
                    color: #1a1a1a !important;
                }

                .dark #doc-body {
                    background-color: #1e293b !important;
                    color: #f1f5f9 !important;
                }

                #doc-body p {
                    text-indent: 2.5rem !important;
                    margin-bottom: 1.5rem !important;
                    line-height: 2.0 !important;
                    font-size: 16px !important;
                }

                #doc-body div {
                    margin-bottom: 1rem !important;
                }

                /* Pemisah halaman (Next Page / Page Break) visual pada tab teks ekstrak */
                .doc-page-break {
                    border-top: 2px dashed #cbd5e1;
                    margin: 3rem 0;
                    position: relative;
                    text-align: center;
                }

                .doc-page-break::after {
                    content: '--- HALAMAN SELANJUTNYA (PAGE BREAK) ---';
                    position: absolute;
                    top: -10px;
                    left: 50%;
                    transform: translateX(-50%);
                    background: #f8fafc;
                    padding: 0 12px;
                    font-size: 10px;
                    font-weight: 700;
                    color: #64748b;
                    letter-spacing: 1px;
                }

                .dark .doc-page-break::after {
                    background: #1e293b;
                    color: #94a3b8;
                }

                mark.t-highlight {
                    padding: 2px 3px;
                    border-radius: 3px;
                    scroll-margin-top: 120px;
                    cursor: pointer;
                    transition: all 0.2s ease-in-out;
                    box-decoration-break: clone;
                    -webkit-box-decoration-break: clone;
                    font-weight: 500;
                }

                mark.t-highlight:hover {
                    filter: brightness(0.9);
                    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.2);
                }

                .t-badge {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 15px;
                    height: 15px;
                    padding: 0 3px;
                    font-size: 9px;
                    font-weight: 800;
                    line-height: 1;
                    color: #ffffff;
                    border-radius: 3px;
                    margin-right: 2px;
                    vertical-align: super;
                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
                }
                </style>
                @endpush

                @push('scripts')
                <script>
                document.addEventListener('DOMContentLoaded', async function() {
                    const fileUrl = @json(($publicMode ?? false) ? asset('storage/' . $check->document->file_path) : route($routePrefix . '.plagiarism.document_docx', $check->id));
                    const rawFilename = @json($check->document->original_filename ?? $check->document->file_path);
                    const fileExt = rawFilename.split('.').pop().toLowerCase();
                    const highlights = @json($highlightsList);

                    const docxLoading = document.getElementById('docx-loading');
                    const docxTarget = document.getElementById('docx-render-target');
                    const docxContainer = document.getElementById('docx-container');
                    const docBody = document.getElementById('doc-body');
                    const tabWord = document.getElementById('tab-word');
                    const tabPlain = document.getElementById('tab-plain');
                    const sidebarPanel = document.getElementById('sidebar-panel');
                    const docContainer = document.getElementById('doc-container');
                    let docxLoaded = false;
                    let docxRenderPromise = null;

                    function loadScript(src) {
                        return new Promise(function(resolve, reject) {
                            const script = document.createElement('script');
                            script.src = src;
                            script.onload = resolve;
                            script.onerror = function() {
                                reject(new Error('Gagal memuat library preview dokumen.'));
                            };
                            document.head.appendChild(script);
                        });
                    }

                    // Toggle Tab Word vs Plain Text
                    if (tabWord && tabPlain) {
                        tabWord.addEventListener('click', function() {
                            tabWord.className =
                                "px-2.5 py-1 rounded-md bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-2xs transition-all";
                            tabPlain.className =
                                "px-2.5 py-1 rounded-md text-slate-600 dark:text-slate-300 hover:text-indigo-600 transition-all";
                            docxContainer.style.display = "flex";
                            docBody.style.display = "none";
                            renderDocx();
                        });
                        tabPlain.addEventListener('click', function() {
                            tabPlain.className =
                                "px-2.5 py-1 rounded-md bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-2xs transition-all";
                            tabWord.className =
                                "px-2.5 py-1 rounded-md text-slate-600 dark:text-slate-300 hover:text-indigo-600 transition-all";
                            docxContainer.style.display = "none";
                            docBody.style.display = "block";
                        });
                    }

                    function syncContainerHeight() {
                        if (window.innerWidth >= 1024 && sidebarPanel && docContainer) {
                            var sidebarHeight = sidebarPanel.offsetHeight;
                            docContainer.style.height = sidebarHeight + 'px';
                        } else if (docContainer) {
                            docContainer.style.removeProperty('height');
                        }
                    }
                    syncContainerHeight();
                    window.addEventListener('resize', syncContainerHeight);

                    async function renderDocx() {
                        if (docxLoaded || docxRenderPromise || fileExt !== 'docx') {
                            return docxRenderPromise;
                        }

                        docxRenderPromise = (async function() {
                            try {
                            await loadScript('https://unpkg.com/jszip/dist/jszip.min.js');
                            await loadScript('https://unpkg.com/docx-preview/dist/docx-preview.min.js');
                            await loadScript('https://cdnjs.cloudflare.com/ajax/libs/mark.js/8.11.1/mark.min.js');

                            if (!window.docx || !window.docx.renderAsync) {
                                throw new Error('Library DOCX preview tidak tersedia.');
                            }

                            const response = await fetch(fileUrl);
                            if (!response.ok) {
                                throw new Error('Dokumen Word gagal dimuat (' + response.status + ')');
                            }
                            const blob = await response.blob();

                            await window.docx.renderAsync(blob, docxTarget, null, {
                                className: "docx",
                                inWrapper: true,
                                ignoreWidth: false,
                                ignoreHeight: false,
                                ignoreFonts: false,
                                breakPages: true,
                                useBase64URL: true
                            });

                            if (docxLoading) docxLoading.style.display = 'none';

                            // Terapkan Stabilo ke dalam dokumen Word asli menggunakan Mark.js
                            if (window.Mark && highlights.length > 0) {
                                const instance = new Mark(docxTarget);

                                highlights.forEach(function(h) {
                                    const cleanText = (h.original_text || '').replace(/\s+/g, ' ')
                                        .trim();
                                    if (cleanText.length < 5) return;

                                    // Ekstrak variasi potongan frasa (30-40 karakter atau kata-kata kunci) agar tidak luput jika Word memecah tag XML
                                    const words = cleanText.split(' ').filter(w => w.length > 2);
                                    const searchPhrases = [cleanText];

                                    if (words.length >= 4) {
                                        searchPhrases.push(words.slice(0, 6).join(' '));
                                        if (words.length >= 10) {
                                            searchPhrases.push(words.slice(4, 10).join(' '));
                                        }
                                    }

                                    searchPhrases.forEach(function(phrase) {
                                        if (!phrase || phrase.length < 6) return;

                                        instance.mark(phrase, {
                                            element: "mark",
                                            className: "t-highlight",
                                            accuracy: "partially",
                                            separateWordSearch: false,
                                            acrossElements: true,
                                            each: function(element) {
                                                element.setAttribute(
                                                    'data-source-id', h
                                                    .source_id);
                                                element.setAttribute(
                                                    'data-source-index', h
                                                    .index);
                                                element.setAttribute(
                                                    'data-source-color', h
                                                    .color);
                                                element.style.backgroundColor =
                                                    h.color + '40';
                                                element.style.borderBottom =
                                                    '2px solid ' + h.color;

                                                if (!element.querySelector(
                                                        '.t-badge')) {
                                                    const sup = document
                                                        .createElement('sup');
                                                    sup.className = 't-badge';
                                                    sup.style.backgroundColor =
                                                        h.color;
                                                    sup.textContent = h.index;
                                                    element.insertBefore(sup,
                                                        element.firstChild);
                                                }
                                            }
                                        });
                                    });
                                });
                            }
                            docxLoaded = true;
                        } catch (err) {
                            console.error("Gagal render docx:", err);
                            if (docxLoading) {
                                docxLoading.innerHTML = '<span class="text-xs font-semibold text-red-600">Dokumen Word gagal dimuat. Gunakan Teks Ekstrak.</span>';
                            }
                        } finally {
                            docxRenderPromise = null;
                        }
                        })();

                        return docxRenderPromise;
                    }

                    if (docxLoading && fileExt !== 'docx') {
                        docxLoading.style.display = 'none';
                    }

                    // Interaksi klik scroll ke sorotan
                    function focusHighlight(target, color) {
                        if (!target) return;
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        target.style.outline = '3px solid ' + (color || '#ff0000');
                        target.style.outlineOffset = '3px';
                        target.style.borderRadius = '4px';
                        setTimeout(function() {
                            target.style.outline = '';
                            target.style.outlineOffset = '';
                        }, 2500);
                    }

                    // Pencari elemen stabilo yang dijamin menemukan target di Word container maupun teks
                    function findBestTarget(sourceId, sourceIndex, snippetText) {
                        const isWordActive = (docxContainer.style.display !== 'none');
                        const primaryContainer = isWordActive ? docxTarget : docBody;
                        const secondaryContainer = isWordActive ? docBody : docxTarget;

                        // 1. Cari exact match data-source-id di container aktif
                        if (sourceId) {
                            let m = primaryContainer.querySelector('mark.t-highlight[data-source-id="' +
                                sourceId + '"]');
                            if (m) return m;
                        }

                        // 2. Cari exact match data-source-index di container aktif
                        if (sourceIndex) {
                            let m = primaryContainer.querySelector('mark.t-highlight[data-source-index="' +
                                sourceIndex + '"]');
                            if (m) return m;
                        }

                        // 3. Cari berdasarkan kemiripan teks kalimat di container aktif
                        if (snippetText) {
                            const words = snippetText.replace(/["'\r\n]/g, '').trim().split(/\s+/).filter(
                                w => w.length > 3);
                            if (words.length > 0) {
                                const sample = words.slice(0, 3).join(' ');
                                const allMarks = primaryContainer.querySelectorAll('mark.t-highlight');
                                for (let i = 0; i < allMarks.length; i++) {
                                    if (allMarks[i].textContent.includes(sample)) {
                                        return allMarks[i];
                                    }
                                }
                            }
                        }

                        // 4. Jika belum ketemu di container utama, cari di container kedua dan pindah tab otomatis
                        if (secondaryContainer) {
                            if (sourceId) {
                                let m = secondaryContainer.querySelector(
                                    'mark.t-highlight[data-source-id="' + sourceId + '"]');
                                if (m) {
                                    if (isWordActive && tabPlain) tabPlain.click();
                                    else if (!isWordActive && tabWord) tabWord.click();
                                    return m;
                                }
                            }
                            if (sourceIndex) {
                                let m = secondaryContainer.querySelector(
                                    'mark.t-highlight[data-source-index="' + sourceIndex + '"]');
                                if (m) {
                                    if (isWordActive && tabPlain) tabPlain.click();
                                    else if (!isWordActive && tabWord) tabWord.click();
                                    return m;
                                }
                            }
                        }

                        return null;
                    }

                    document.querySelectorAll('[data-source-id]').forEach(function(item) {
                        item.addEventListener('click', function() {
                            const sourceId = item.getAttribute('data-source-id');
                            const sourceIndex = item.getAttribute('data-source-index');
                            const sourceColor = item.getAttribute('data-source-color');
                            const target = findBestTarget(sourceId, sourceIndex, null);
                            if (target) {
                                focusHighlight(target, sourceColor);
                            }
                        });
                    });

                    document.querySelectorAll('[data-highlight-source-id]').forEach(function(item) {
                        item.addEventListener('click', function() {
                            const sourceId = item.getAttribute('data-highlight-source-id');
                            const rawText = item.getAttribute('data-highlight-text');
                            const color = item.getAttribute('data-highlight-color') ||
                                '#ff0000';
                            const target = findBestTarget(sourceId, null, rawText);
                            if (target) {
                                focusHighlight(target, color);
                            }
                        });
                    });

                    document.addEventListener('click', function(e) {
                        const markEl = e.target.closest('mark.t-highlight');
                        if (markEl) {
                            focusHighlight(markEl, markEl.getAttribute('data-source-color') ||
                                '#ff0000');
                        }
                    });
                });
                </script>
                @endpush
