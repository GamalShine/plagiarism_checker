<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Originality Report - {{ $check->document->original_filename }}</title>
    <style>
        @font-face {
            font-family: 'Arial';
            font-style: normal;
            font-weight: 400;
            src: url("{{ str_replace('\\', '/', storage_path('app/fonts/arial.ttf')) }}") format('truetype');
        }
        @font-face {
            font-family: 'Arial';
            font-style: normal;
            font-weight: 700;
            src: url("{{ str_replace('\\', '/', storage_path('app/fonts/arialbd.ttf')) }}") format('truetype');
        }
        @page { margin: 9mm 7mm; }
        body { color: #111827; font-family: 'Times New Roman', Times, serif; font-size: 12px; line-height: 1.5; margin: 0; }
        .highlighted-document { border: 0; margin-bottom: 18px; padding: 0; font-family: 'Times New Roman', Times, serif !important; font-size: 11px; line-height: 1.55; overflow-wrap: anywhere; }
        .highlighted-document * { font-family: 'Times New Roman', Times, serif !important; }
        .report-after-document { }
        .highlighted-document .doc-paragraph { margin: 0 0 10px; text-align: justify; }
        .highlighted-document .doc-paragraph:last-child { margin-bottom: 0; }
        .highlighted-document .doc-heading { color: #111827; font-weight: 700; margin: 16px 0 10px; text-transform: none; }
        .highlighted-document .doc-heading-1 { margin-left: 0; margin-right: 0; text-align: center; text-transform: uppercase; }
        .highlighted-document .doc-heading-2 { margin-left: 0; margin-right: 0; text-align: left; }
        .highlighted-document .doc-heading-3 { margin-left: 0.5in; margin-right: 0; text-align: left; }
        .highlighted-document mark.t-highlight { border-radius: 1px; color: #111827; padding: 0 1px; }
        .highlighted-document .t-badge { color: #ffffff; display: inline-block; font-family: Arial, Helvetica, sans-serif !important; font-size: 8px; font-weight: 700; line-height: 1; margin: 0 2px 0 0; padding: 1px 3px; vertical-align: super; white-space: nowrap; }
        .highlighted-document .t-badge-main { margin-left: 1em; }
        .highlighted-document .t-badge-sub { margin-left: 2em; }
        .file-header, .report-label, .summary-grid, .primary-label, .source-row { page-break-before: auto; page-break-after: auto; font-family: 'Arial'; }
        .file-header { border-bottom: 2px solid #111827; color: #111827; font-size: 20px; line-height: 1.25; margin-bottom: 6px; padding-bottom: 4px; word-break: break-word; }
        .report-label { border-bottom: 1px solid #111827; color: #4b5563; font-size: 10px; font-weight: 400; letter-spacing: .8px; padding: 4px 0 6px; text-transform: uppercase; }
        .summary-grid { border-bottom: 2px solid #111827; border-collapse: collapse; display: table; margin: 0 0 2px; table-layout: fixed; width: 100%; }
        .summary-cell { display: table-cell; padding-right: 8px; vertical-align: top; width: 33.33%; }
        .summary-cell:last-child { padding-right: 0; }
        .summary-value { color: #111827; font-family: 'Arial'; font-size: 40px; font-weight: 400; line-height: 1; padding-top: 10px; }
        .summary-value.primary { color: #111827; }
        .summary-value.originality { color: #0f766e; }
        .summary-label { color: #4b5563; font-family: 'Arial'; font-size: 10px; letter-spacing: .8px; margin-bottom: 12px; text-transform: uppercase; }
        .primary-label { border-bottom: 1px solid #111827; color: #374151; font-size: 10px; font-weight: 600; letter-spacing: .8px; padding: 4px 0 6px; text-transform: uppercase; }
        .source-row { border-bottom: 1px solid #e5e7eb; display: block; min-height: 56px; padding: 12px 0; position: relative; page-break-inside: avoid; }
        .source-index { border-radius: 2px; color: #fff; display: inline-block; font-family: 'Arial'; font-size: 15px; font-weight: 700; height: 32px; left: 0; line-height: 32px !important; padding: 0; position: absolute; text-align: center; text-indent: 0; top: 34px; vertical-align: middle; width: 32px; }
        .source-info-cell { margin-left: 48px; margin-right: 180px; }
        .source-title { color: #111827; font-family: 'Arial'; font-size: 15px; line-height: 1.45; overflow-wrap: anywhere; }
        .source-meta { color: #6b7280; font-family: 'Arial'; font-size: 10px; margin-top: 4px; }
        .source-stats-cell { position: absolute; right: 0; text-align: right; top: 16px; width: 170px; white-space: nowrap; }
        .source-words { color: #4b5563; font-family: 'Arial'; font-size: 13px; }
        .source-percent { color: #111827; font-family: 'Arial'; font-size: 24px; }
        .empty-state { border: 1px dashed #d1d5db; color: #6b7280; font-size: 12px; margin-top: 10px; padding: 30px 10px; text-align: center; }
        .footer { border-top: 1px solid #e5e7eb; color: #878c94; font-size: 11px; line-height: 1.6; margin-top: 30px; padding-top: 12px; }
    </style>
</head>
<body>
    @if(trim($highlightedText ?? '') !== '')
    <div class="highlighted-document">{!! $highlightedText !!}</div>
    @endif

    <div class="report-after-document">
    <div class="file-header">{{ $check->document->original_filename }}</div>
    <div class="report-label">Originality Report</div>

    <div class="summary-grid">
        <div class="summary-cell">
            <div class="summary-value primary">{{ (int) round($check->total_similarity) }}<span style="font-size:26px;">%</span></div>
            <div class="summary-label">Overall Similarity</div>
        </div>
        <div class="summary-cell">
            <div class="summary-value">{{ $check->sources_count }}</div>
            <div class="summary-label">Sources Found</div>
        </div>
        <div class="summary-cell">
            <div class="summary-value originality">{{ (int) round($check->originality) }}<span style="font-size:26px;">%</span></div>
            <div class="summary-label">Originality</div>
        </div>
    </div>

    <div class="primary-label">Primary Sources</div>
    @php
        $visibleSources = $check->sources->filter(fn ($source) => $source->matched_words < 1000 && $source->matched_words > 0);
        $palette = [
            '#DE60E5', // Pink
            '#D763FF', // Ungu
            '#25B3B3', // Cyan
            '#0A9D02', // Hijau
            '#A47108', // Olive
            '#7A2F08', // Cokelat
            '#0A476F', // Biru tua
            '#9C449B', // Magenta tua
            '#808080', // Abu-abu
        ];
    @endphp
    @if($visibleSources->isEmpty())
        <div class="empty-state">Tidak ditemukan kemiripan signifikan. Dokumen bersih dari plagiarisme.</div>
    @else
        @foreach($visibleSources as $index => $source)
            @php
                $title = trim((string) ($source->title ?? ''));
                if ($title === '' || mb_strtolower($title) === 'no title') {
                    $url = (string) ($source->url ?? '');
                    $parsed = $url !== '' && $url !== '#' ? parse_url($url) : [];
                    $title = $parsed['host'] ?? ($source->source_label ?? 'Unknown Source');
                    if (! empty($parsed['path']) && $parsed['path'] !== '/') $title .= rtrim($parsed['path'], '/');
                }
                $title = mb_strlen($title) > 170 ? mb_substr($title, 0, 167) . '...' : $title;
                $color = $palette[$index % count($palette)];
            @endphp
            <div class="source-row">
                <span class="source-index" style="background-color: {{ $color }};">{{ $source->turnitin_index ?? ($index + 1) }}</span>
                <div class="source-info-cell">
                    <div class="source-title" style="color: {{ $color }};">{{ $title }}</div>
                    <div class="source-meta">{{ $source->source_label ?? $source->source_name ?? 'Internet' }}</div>
                </div>
                <div class="source-stats-cell">
                    <span class="source-words">{{ $source->matched_words }} words &mdash;</span>
                    <span class="source-percent">{{ $source->turnitin_percentage }}</span>
                </div>
            </div>
        @endforeach
    @endif

    <div class="footer">
        EXCLUDE QUOTES OFF &nbsp;&nbsp; EXCLUDE SOURCES OFF<br>
        EXCLUDE BIBLIOGRAPHY ON &nbsp;&nbsp; EXCLUDE MATCHES OFF
    </div>
    </div>
</body>
</html>
