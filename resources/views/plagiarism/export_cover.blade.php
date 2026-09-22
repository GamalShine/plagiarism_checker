@php
    $hangulFontPath = str_replace('\\', '/', storage_path('app/fonts/malgun.ttf'));
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Originality Report - {{ $check->document->title }}</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #202124;
            margin: 0;
            padding: 0;
            text-align: left;
        }

        .cover-page {
            padding: 52mm 18mm 22mm;
            box-sizing: border-box;
        }

        .report-title {
            color: #202124;
            font-family: Tahoma, Arial, Helvetica, sans-serif;
            font-size: 20px;
            font-weight: bold;
            line-height: 1.15;
            margin: 0 0 13px;
        }

        .document-title {
            color: #202124;
            font-family: Verdana, Arial, Helvetica, sans-serif;
            font-size: 17px;
            font-weight: bold;
            line-height: 1.25;
            margin: 0 0 8px;
            word-break: break-word;
        }

        .muted {
            color: #777;
            font-size: 8px;
            line-height: 1.4;
        }

        .metadata-line {
            font-family: 'Malgun Gothic', Arial, Helvetica, sans-serif;
            font-size: 8px;
        }

        @font-face {
            font-family: 'Malgun Gothic';
            src: url('{{ $hangulFontPath }}') format('truetype');
        }

        .file-icon {
            display: inline-block;
            width: 6px;
            height: 7px;
            border: 1px solid #777;
            vertical-align: -1px;
            margin-right: 3px;
        }

        .similarity {
            color: {{ $check->similarity_color }};
            font-size: 18px;
            font-weight: 700;
            line-height: 1.2;
            margin: 0 0 8px;
        }

        .divider {
            border-bottom: 1px solid #d1d5db;
            margin: 0 0 18px;
        }

        .section-title {
            color: #202124;
            font-family: 'Arial Black', Arial, Helvetica, sans-serif;
            font-size: 12px;
            font-weight: 700;
            padding-top: 0;
            margin: 22px 0 16px;
        }

        .details {
            width: 58%;
            border-collapse: collapse;
            font-family: 'Arial Black', Arial, Helvetica, sans-serif;
            font-size: 11px;
        }

        .details td {
            padding: 8px 0;
            vertical-align: top;
        }

        .details td:first-child {
            width: 43%;
            color: #777;
            font-weight: normal;
        }

        .detail-label {
            display: block;
            color: #6b7280;
            font-family: 'Arial Black', Arial, Helvetica, sans-serif;
            font-size: 11px;
            font-weight: 400;
            margin-bottom: 7px;
        }

        .details td:last-child {
            color: #202124;
            font-weight: 700;
        }

        .stats {
            position: absolute;
            top: 373px;
            right: 125px;
            width: 125px;
            background: #f7f7f7;
            border-collapse: collapse;
            font-family: 'Arial Black', Arial, Helvetica, sans-serif;
            font-size: 11px;
        }

        .stats td {
            padding: 9px 12px;
            color: #333;
            font-weight: 700;
        }
    </style>
</head>

<body>
    @php
        $content = (string) ($check->document->content ?? '');
        $characterCount = mb_strlen($content);
        $pageCount = max(1, (int) ceil($check->total_words / 500));
        $documentIdSuffix = str_pad((string) $check->document_id, 3, '0', STR_PAD_LEFT);
    @endphp

    <div class="cover-page">
        <h1 class="report-title">Cek Turnitin</h1>
        <div class="document-title">{{ $check->document->title ?: $check->document->original_filename }}</div>
        <div class="muted metadata-line">논문 및 과제 검사 - 유사도 검사 시 DB 미 저장 (Originality Check - No Repository)</div>

        <div class="divider"></div>
        <div class="similarity">{{ (int) round($check->total_similarity) }}% Overall Similarity</div>
        <div class="divider"></div>
        <div class="section-title">Document Details</div>
        <table class="details">
            <tr>
                <td><span class="detail-label">Submission ID</span><strong>trn:oid:::9817:193844{{ $documentIdSuffix }}</strong></td>
            </tr>
            <tr><td><span class="detail-label">Submission Date</span><strong>{{ $check->created_at->format('d M Y, H:i') }}</strong></td></tr>
            <tr><td><span class="detail-label">Download Date</span><strong>{{ now()->format('d M Y, H:i') }}</strong></td></tr>
            <tr><td><span class="detail-label">File Name</span><strong>{{ $check->document->original_filename }}</strong></td></tr>
            <tr><td><span class="detail-label">File Size</span><strong>{{ $check->document->file_size_formatted }}</strong></td></tr>
        </table>

        <table class="stats">
            <tr><td>{{ $pageCount }} Pages</td></tr>
            <tr><td>{{ number_format($check->total_words) }} Words</td></tr>
            <tr><td>{{ number_format($characterCount) }} Characters</td></tr>
        </table>
    </div>
</body>

</html>
