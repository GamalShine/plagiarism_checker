<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Originality Report - {{ $check->document->original_filename }}</title>
    <style>
    @page {
        margin: 9mm 7mm;
    }

    body {
        font-family: Helvetica, Arial, 'Helvetica Neue', sans-serif;
        color: #111827;
        line-height: 1.5;
        margin: 0;
        padding: 0;
        font-size: 12px;
    }

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
        margin-top: 0px;
        margin-bottom: 0px;
        padding-top: -2;
        padding-bottom: 6px;
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
        border-bottom: 2px solid #111827;
    }

    .summary-value {
        font-size: 44px;
        font-weight: 400;
        color: #111827;
        line-height: 1;
        letter-spacing: -1px;
        margin: 0;
        padding: 10px 0 0 0;
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
        padding: 10px 0 0 0;
    }

    .summary-value.originality {
        color: #0f766e;
        font-weight: 400;
        margin: 0;
        padding: 10px 0 0 0;
    }

    .summary-label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #4b5563;
        margin-top: 0;
        margin-bottom: 12px;
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
        margin-top: 0px;
        margin-bottom: 0px;
        padding-top: -2;
        padding-bottom: 6px;
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
    }

    .source-title a,
    .source-title {
        color: inherit;
        text-decoration: none;
    }

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

    .highlight-list {
        margin-top: 18px;
    }

    .highlight-item {
        margin-bottom: 8px;
        padding: 7px 9px;
        border-left: 4px solid #f59e0b;
        background-color: #fff3a3;
        color: #1f2937;
        font-size: 10px;
        line-height: 1.45;
    }

    .highlight-source {
        font-size: 9px;
        font-weight: 700;
        color: #6b7280;
        margin-bottom: 2px;
    }

    .highlighted-document {
        margin-top: 18px;
        padding: 10px;
        border: 1px solid #e5e7eb;
        font-family: 'Times New Roman', Times, serif;
        font-size: 10px;
        line-height: 1.55;
        text-align: justify;
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

    {{-- Paling atas: Nama file kayak di foto Turnitin --}}
    <div class="file-header">{{ $check->document->original_filename }}</div>
    <div class="report-label">Originality Report</div>

    {{-- Kolom Summary: Overall Similarity | Sources Found | Originality --}}
    <div class="summary-grid">
        <div class="summary-cell" style="width: 33.33%;">
            <div class="summary-value primary" style="font-size: 40px;">{{ (int) round($check->total_similarity) }}<span
                    style="font-size:26px;">%</span></div>
            <div class="summary-label">Overall Similarity</div>
        </div>
        <div class="summary-cell" style="width: 33.33%;">
            <div class="summary-value" style="font-size: 40px;">{{ $check->sources_count }}</div>
            <div class="summary-label">Sources Found</div>
        </div>
        <div class="summary-cell" style="width: 33.33%;">
            <div class="summary-value originality" style="font-size: 40px;">{{ (int) round($check->originality) }}<span
                    style="font-size:26px;">%</span></div>
            <div class="summary-label">Originality</div>
        </div>
    </div>

    {{-- PRIMARY SOURCES --}}
    <div class="primary-label">Primary Sources</div>

    @php
    $visibleSources = $check->sources->filter(fn($s) => $s->matched_words < 1000 && $s->matched_words > 0);
        @endphp

        @if($visibleSources->isEmpty())
        <div class="empty-state">Tidak ditemukan kemiripan signifikan. Dokumen bersih dari plagiarisme.</div>
        @else
        @php
        $turnitinPalette = [
        '#ef4444',
        '#d946ef',
        '#8b5cf6',
        '#14b8a6',
        '#22c55e',
        '#ca8a04',
        '#92400e',
        '#1e40af',
        '#a855f7',
        '#65a30d',
        '#312e81',
        ];
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
                $rowColor = $turnitinPalette[$idx % count($turnitinPalette)];
                @endphp
                <tr class="source-row">
                    <td class="source-index-cell">
                        <span class="source-index"
                            style="background-color: {{ $rowColor }};">{{ $source->turnitin_index ?? ($idx + 1) }}</span>
                    </td>
                    <td class="source-info-cell">
                        <div class="source-title" style="color: {{ $rowColor }};">{{ htmlspecialchars($displayTitle) }}
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

        <div class="footer"
            style="text-align: left; font-size: 11px; color: #6b7280; opacity: 0.5; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 12px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 11px; color: #6b7280;">
                <tr>
                    <td style="width: 50%; padding: 2px 0;"><strong style="color: #4b5563;">EXCLUDE QUOTES</strong> OFF
                    </td>
                    <td style="width: 50%; padding: 2px 0;"><strong style="color: #4b5563;">EXCLUDE SOURCES</strong> OFF
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%; padding: 2px 0;"><strong style="color: #4b5563;">EXCLUDE
                            BIBLIOGRAPHY</strong> ON</td>
                    <td style="width: 50%; padding: 2px 0;"><strong style="color: #4b5563;">EXCLUDE MATCHES</strong> OFF
                    </td>
                </tr>
            </table>
        </div>

</body>

</html>
