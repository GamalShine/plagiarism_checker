<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Originality Report - {{ $check->document->original_filename }}</title>
    <style>
        @page {
            margin: 4.5mm 3.5mm;
        }

        body {
            font-family: Helvetica, Arial, 'Helvetica Neue', sans-serif;
            color: #1f2937;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }

        .page-break {
            page-break-after: always;
        }

        .page-break:last-child {
            page-break-after: auto;
        }

        /* ===== COVER PAGE ===== */
        .cover {
            text-align: center;
            padding: 40px 20px 30px;
        }

        .cover-logo {
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 24px;
        }

        .cover-title {
            font-family: 'Lucida Sans Unicode', 'Lucida Sans', 'Lucida Grande', sans-serif;
            font-size: 24px;
            font-weight: normal !important;
            color: #111827;
            margin: 0;
            line-height: 1.35;
            word-break: break-word;
        }

        .cover-subtitle {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 28px;
            word-break: break-word;
        }

        .cover-score {
            display: inline-block;

            border: 2px solid {
                    {
                    $check->similarity_color
                }
            }

            ;
            border-radius: 8px;
            padding: 18px 36px;
            margin: 10px 0 24px;
        }

        .cover-score-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .cover-score-value {
            font-size: 42px;
            font-weight: 800;

            color: {
                    {
                    $check->similarity_color
                }
            }

            ;
            line-height: 1;
        }

        .cover-meta {
            width: 100%;
            max-width: 520px;
            margin: 0 auto;
            border-collapse: collapse;
            text-align: left;
        }

        .cover-meta td {
            padding: 7px 0;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .cover-meta td:first-child {
            width: 38%;
            color: #6b7280;
            font-weight: 600;
        }

        /* ===== DOCUMENT PAGE PREVIEW ===== */
        .doc-page {
            text-align: center;
            margin-bottom: 10px;
        }

        .doc-page img {
            width: 85%;
            max-width: 85%;
            height: auto;
            margin: 0 auto;
            border: 1px solid #d1d5db;
        }

        .doc-page-label {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 6px;
            text-align: right;
        }

        /* ===== TURNITIN-STYLE REPORT PAGE ===== */
        .file-header {
            font-size: 20px;
            font-weight: 400;
            color: #111827;
            padding-bottom: 4px;
            border-bottom: 2px solid #111827;
            margin-bottom: 6px;
            word-break: break-word;
            line-height: 1.25;
        }

        .report-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #4b5563;
            margin-top: 2px;
            margin-bottom: 0;
            padding-top: 0;
            padding-bottom: 0;
            border-bottom: 1px solid #111827;
            font-weight: 400;
        }

        .summary-grid {
            display: table;
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-top: 0;
            margin-bottom: 2px;
            padding-top: 0;
            padding-bottom: 4px;
            border-bottom: 1px solid #111827;
        }

        .summary-cell {
            display: table-cell;
            width: 25%;
            vertical-align: top;
            padding: 0 8px 0 0;
        }

        .summary-cell:last-child {
            padding-right: 0;
        }

        .summary-value {
            font-size: 44px;
            font-weight: 400;
            color: #111827;
            line-height: 1;
            letter-spacing: -1px;
            margin: 0;
            padding: 0;
        }

        .summary-value.primary {
            color: {
                    {
                    $check->similarity_color
                }
            }

            ;
            font-size: 56px;
            font-weight: 400;
            margin: 0;
            padding: 0;
        }

        .summary-value.originality {
            color: #0f766e;
            font-weight: 400;
            margin: 0;
            padding: 0;
        }

        .summary-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #4b5563;
            margin-top: 0;
            font-weight: 400;
        }

        .summary-sub {
            font-size: 11px;
            color: #6b7280;
            margin-top: 2px;
            font-weight: 400;
        }

        .primary-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #4b5563;
            margin-top: 2px;
            margin-bottom: 2px;
            padding-top: 0;
            padding-bottom: 2px;
            font-weight: 400;
            border-bottom: 1px solid #111827;
        }

        .source-list {
            width: 100%;
            border-collapse: collapse;
        }

        .source-row {
            border-bottom: 1px solid #e5e7eb;
        }

        .source-row:last-child {
            border-bottom: none;
        }

        .source-index-cell {
            width: 44px;
            padding: 14px 10px 14px 0;
            vertical-align: middle;
            text-align: center;
        }

        .source-index {
            display: inline-block;
            width: 32px;
            height: 32px;
            line-height: 32px;
            text-align: center;
            vertical-align: middle;
            color: #ffffff;
            font-weight: 700;
            font-size: 15px;
            border-radius: 2px;
            padding: 0;
            margin: 0 auto;
        }

        .source-info-cell {
            padding: 12px 10px;
            vertical-align: middle;
            text-align: left;
        }

        .source-title {
            font-size: 15px;
            font-weight: 400;
            color: #111827;
            line-height: 1.45;
            word-break: break-word;
            text-align: left;
        display: -webkit-box;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;

        .source-meta {
            font-size: 10px;
            color: #6b7280;
            margin-top: 4px;
            font-weight: 400;
            text-transform: none;
            text-align: left;
        }

        .source-stats-cell {
            width: 170px;
            padding: 14px 0 14px 10px;
            text-align: right;
            vertical-align: top;
            white-space: nowrap;
        }

        .source-words {
            font-size: 14px;
            color: #374151;
            font-weight: 400;
        }

        .source-sep {
            font-size: 14px;
            color: #9ca3af;
            margin: 0 4px;
            font-weight: 400;
        }

        .source-percent {
            font-size: 24px;
            font-weight: 400;
            color: #111827;
        }

        .empty-state {
            padding: 30px 10px;
            color: #6b7280;
            font-size: 12px;
            text-align: center;
            border: 1px dashed #d1d5db;
            border-radius: 6px;
            margin-top: 10px;
        }

        .fallback-note {
            background: #fff7ed;
            border: 1px solid #fdba74;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 11px;
            color: #9a3412;
        }

        /* ===== HIGHLIGHTED TEXT ===== */
        .highlight-section-label {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
            margin: 32px 0 6px;
            padding-bottom: 6px;
            border-bottom: 2px solid #4a4a8a;
        }

        .highlight-section-desc {
            font-size: 11px;
            color: #666;
            margin-bottom: 16px;
        }

        .content {
            font-family: 'Times New Roman', Times, serif;
            font-size: 13px;
            text-align: justify;
            white-space: pre-wrap;
            line-height: 1.8;
        }

        mark.t-highlight {
            color: inherit;
            padding: 1px 0;
        }

        sup.t-badge {
            color: white;
            padding: 1px 4px;
            border-radius: 3px;
            font-weight: 700;
            font-size: 9px;
        }

        .footer {
            margin-top: 44px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            line-height: 1.6;
        }
    </style>
</head>

<body>

    {{-- HALAMAN 1: COVER --}}
    <div class="cover page-break" style="padding-top: 120px;">
        <div class="cover-title"
            style="font-family: 'Lucida Sans Unicode', 'Lucida Sans', sans-serif; font-size: 24px; font-weight: normal !important; margin-bottom: 24px;">
            {{ $check->document->title }}
        </div>

        <div style="font-size: 64px; font-weight: bold; color: {{ $check->similarity_color }}; margin-bottom: 40px;">
            {{ (int) round($check->total_similarity) }}%
        </div>

        <table style="margin: 0 auto; text-align: left; font-size: 13px; color: #4b5563; border-collapse: collapse; min-width: 300px;">
            <tr>
                <td style="padding: 10px 24px 10px 0; font-weight: 600; border-bottom: 1px solid #e5e7eb; color: #111827;">Status</td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">{{ $check->similarity_label }}</td>
            </tr>
            <tr>
                <td style="padding: 10px 24px 10px 0; font-weight: 600; border-bottom: 1px solid #e5e7eb; color: #111827;">Word Count</td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">{{ number_format($check->total_words) }} words</td>
            </tr>
            <tr>
                <td style="padding: 10px 24px 10px 0; font-weight: 600; border-bottom: 1px solid #e5e7eb; color: #111827;">Time Submitted</td>
                <td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">{{ $check->created_at->format('M d, Y g:i A') }}</td>
            </tr>
        </table>
    </div>

    {{-- HALAMAN 2..N: DOKUMEN ASLI (JIKA ADA) --}}
    @if(!empty($pageImages))
        @foreach($pageImages as $idx => $pageImage)
            @php
                $imagePath = is_array($pageImage) ? ($pageImage['path'] ?? '') : $pageImage;
                $isLast = $loop->last;
            @endphp
            @if($imagePath)
                <div class="doc-page @if(!$isLast) page-break @endif">
                    <div class="doc-page-label">Halaman {{ $idx + 1 }}</div>
                    <img src="file://{{ str_replace('\\', '/', $imagePath) }}" alt="Halaman {{ $idx + 1 }}">
                </div>
            @endif
        @endforeach
        {{-- Page break sebelum report kalo masih ada gambar --}}
        <div style="page-break-after: always;"></div>
    @endif

    {{-- HALAMAN REPORT: TURNITIN STYLE ORIGINALITY REPORT --}}
    <div>

        @if(empty($pageImages))
            <div class="fallback-note">
                Pratinjau halaman dokumen tidak tersedia. Pastikan Python dengan PyMuPDF terpasang
                (<code>pip install pymupdf</code>) agar halaman Word/PDF ditampilkan sebagai gambar.
            </div>
        @endif

        {{-- Paling atas: Nama file kayak di foto Turnitin --}}
        <div class="file-header">{{ $check->document->original_filename }}</div>
        <div class="report-label">Originality Report</div>

        {{-- Kolom Summary: Overall Similarity | Sources Found | Originality --}}
        <div class="summary-grid">
            <div class="summary-cell" style="width: 33.33%;">
                <div class="summary-value primary" style="font-size: 40px;">
                    {{ (int) round($check->total_similarity) }}<span style="font-size:26px;">%</span>
                </div>
                <div class="summary-label">Overall Similarity</div>
            </div>
            <div class="summary-cell" style="width: 33.33%;">
                <div class="summary-value" style="font-size: 40px;">{{ $check->sources_count }}</div>
                <div class="summary-label">Sources Found</div>
            </div>
            <div class="summary-cell" style="width: 33.33%;">
                <div class="summary-value originality" style="font-size: 40px;">
                    {{ (int) round($check->originality) }}<span style="font-size:26px;">%</span>
                </div>
                <div class="summary-label">Originality</div>
            </div>
        </div>

        {{-- PRIMARY SOURCES --}}
        <div style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.8px; color: #374151; opacity: 1; font-weight: 700; margin: 0 0 8px; border-bottom: 1px solid #111827; padding-bottom: 6px;">PRIMARY SOURCES</div>
        @php
            $visibleSources = $check->sources->filter(fn($s) => $s->matched_words < 1000 && $s->matched_words > 0);
        @endphp

        @if($visibleSources->isEmpty())
            <div class="empty-state">Tidak ditemukan kemiripan signifikan. Dokumen bersih dari plagiarisme.</div>
        @else
            @php
                $turnitinPalette = [
                    '#EF4444',
                    '#3B82F6',
                    '#10B981',
                    '#F59E0B',
                    '#8B5CF6',
                    '#14B8A6',
                    '#EC4899',
                    '#F97316',
                    '#6366F1',
                    '#06B6D4',
                    '#64748B',
                ];
                $truncatePdfTitle = function (?string $value, int $maxChars = 170): string {
                    $text = trim((string) ($value ?? ''));
                    if ($text === '') {
                        return '';
                    }

                    $text = preg_replace('/\s+/', ' ', $text);
                    if ($text === null) {
                        return '';
                    }

                    if (mb_strlen($text) <= $maxChars) {
                        return $text;
                    }

                    $trimmed = rtrim(mb_substr($text, 0, $maxChars - 3));
                    return $trimmed . '...';
                };
            @endphp
            <table class="source-list" cellpadding="0" cellspacing="0">
                <tbody>
                    @foreach($visibleSources as $idx => $source)
                        @php
                            $displayTitle = trim($source->title ?? '');
                            if ($displayTitle === '' || mb_strtolower($displayTitle) === 'no title') {
                                $url = $source->url ?? '';
                                if ($url && $url !== '#') {
                                    $parsed = parse_url($url);
                                    $displayTitle = $parsed['host'] ?? $url;
                                    if (!empty($parsed['path']) && $parsed['path'] !== '/') {
                                        $displayTitle .= rtrim($parsed['path'], '/');
                                    }
                                } else {
                                    $displayTitle = $source->source_label ?? 'Unknown Source';
                                }
                            }
                            $sourceLabel = $source->source_label ?? $source->source_name ?? 'Internet';
                            $rowColor = $source->color_code ?? $turnitinPalette[$idx % count($turnitinPalette)];
                            $displayTitle = $truncatePdfTitle($displayTitle, 170);
                        @endphp
                        <tr class="source-row">
                            <td class="source-index-cell">
                                <span class="source-index"
                                    style="background-color: {{ $rowColor }}; color: #ffffff; font-weight: 400; box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.08);">{{ $source->turnitin_index ?? ($idx + 1) }}</span>
                            </td>
                            <td class="source-info-cell">
                                <div class="source-title" style="color: {{ $rowColor }}; font-weight: 400; opacity: 1;">{{ htmlspecialchars($displayTitle) }}
                                </div>
                                <div class="source-meta">{{ htmlspecialchars($sourceLabel) }}</div>
                            </td>
                            <td class="source-stats-cell">
                                <span class="source-words"
                                    style="font-size: 13px; color: #4b5563; font-weight: normal; margin-right: 4px;">{{ $source->matched_words }}
                                    words &mdash;</span>
                                <span class="source-percent">{{ $source->turnitin_percentage }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="footer" style="text-align: left; font-size: 11px; color: #6b7280; opacity: 1; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 12px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 11px; color: #6b7280;">
                <tr>
                    <td style="width: 50%; padding: 2px 0;"><strong style="color: #4b5563; opacity: 0.8;">EXCLUDE QUOTES</strong> OFF</td>
                    <td style="width: 50%; padding: 2px 0;"><strong style="color: #4b5563; opacity: 0.8;">EXCLUDE SOURCES</strong> OFF</td>
                </tr>
                <tr>
                    <td style="width: 50%; padding: 2px 0;"><strong style="color: #4b5563; opacity: 0.8;">EXCLUDE BIBLIOGRAPHY</strong> ON</td>
                    <td style="width: 50%; padding: 2px 0;"><strong style="color: #4b5563; opacity: 0.8;">EXCLUDE MATCHES</strong> OFF</td>
                </tr>
            </table>
        </div>
    </div>

</body>

</html>
