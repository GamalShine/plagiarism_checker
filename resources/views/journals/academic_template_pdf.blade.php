<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $journal->title }}</title>
    <style>
        @page {
            margin: 25mm 20mm 25mm 20mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #111;
            margin: 0;
            padding: 0;
        }
        .header-meta {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            color: #555;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin-bottom: 18px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .title {
            font-size: 15pt;
            font-weight: bold;
            text-align: center;
            line-height: 1.25;
            margin-bottom: 12px;
            color: #000;
        }
        .authors {
            font-size: 10.5pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 4px;
        }
        .affiliations {
            font-size: 9pt;
            font-style: italic;
            text-align: center;
            color: #444;
            margin-bottom: 4px;
        }
        .email {
            font-size: 8.5pt;
            text-align: center;
            color: #0044cc;
            margin-bottom: 18px;
        }
        .abstract-container {
            border-top: 1px solid #222;
            border-bottom: 1px solid #222;
            padding: 10px 15px;
            margin-bottom: 20px;
            background-color: #fafafa;
        }
        .abstract-title {
            font-size: 9.5pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }
        .abstract-text {
            font-size: 9pt;
            text-align: justify;
            line-height: 1.4;
            font-style: italic;
        }
        .keywords {
            font-size: 8.5pt;
            margin-top: 6px;
            font-style: normal;
        }
        .section-header {
            font-size: 10.5pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 14px;
            margin-bottom: 6px;
            border-bottom: 1px solid #eee;
            padding-bottom: 2px;
        }
        .section-body {
            font-size: 10pt;
            text-align: justify;
            line-height: 1.4;
            margin-bottom: 12px;
            text-indent: 1.5em;
        }
        .references {
            font-size: 8.5pt;
            line-height: 1.35;
        }
    </style>
</head>
<body>

    <div class="header-meta">
        <span>{{ $journal->template_name }}</span> &bull; <span>Publikasi Naskah Ilmiah Terstandarisasi</span>
    </div>

    <div class="title">{{ $journal->title }}</div>
    <div class="authors">{{ $journal->author }}</div>
    
    @if($journal->institution)
        <div class="affiliations">{{ $journal->institution }}</div>
    @endif
    
    @if($journal->email)
        <div class="email">Korespondensi: {{ $journal->email }}</div>
    @endif

    <div class="abstract-container">
        <div class="abstract-title">Abstrak / Abstract</div>
        <div class="abstract-text">{{ $journal->abstract }}</div>
        <div class="keywords"><strong>Kata Kunci / Keywords:</strong> {{ $journal->keywords }}</div>
    </div>

    @php
        $sections = is_array($journal->content) ? $journal->content : json_decode($journal->content ?? '[]', true);
    @endphp
    
    @foreach($sections as $idx => $sec)
        <div class="section-header">{{ $idx + 1 }}. {{ strtoupper($sec['title'] ?? '') }}</div>
        <div class="section-body">{{ $sec['content'] ?? '' }}</div>
    @endforeach

</body>
</html>
