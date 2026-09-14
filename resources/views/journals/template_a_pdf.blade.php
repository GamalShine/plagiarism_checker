<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $journal->title }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 30px;
        }
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .font-bold { font-weight: bold; }
        .italic { font-style: italic; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .mb-4 { margin-bottom: 20px; }
        .mt-4 { margin-top: 20px; }
        .title {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        .author {
            font-size: 12pt;
            text-align: center;
            margin-bottom: 5px;
        }
        .institution {
            font-size: 10pt;
            font-style: italic;
            text-align: center;
            margin-bottom: 5px;
        }
        .email {
            font-size: 10pt;
            text-align: center;
            margin-bottom: 30px;
        }
        .abstract-title {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }
        .abstract-content {
            font-size: 11pt;
            font-style: italic;
            text-align: justify;
            margin-bottom: 10px;
        }
        .keywords {
            font-size: 10pt;
            font-style: italic;
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .section-content {
            font-size: 12pt;
            text-align: justify;
            margin-bottom: 15px;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>

    <div class="title">{{ $journal->title }}</div>
    <div class="author">{{ $journal->author }}</div>
    
    @if($journal->institution)
        <div class="institution">{{ $journal->institution }}</div>
    @endif
    
    @if($journal->email)
        <div class="email">{{ $journal->email }}</div>
    @endif

    <div class="abstract-title">ABSTRACT</div>
    <div class="abstract-content">{{ $journal->abstract }}</div>
    <div class="keywords"><span class="font-bold">Keywords:</span> {{ $journal->keywords }}</div>

    @php
        $sections = is_array($journal->content) ? $journal->content : json_decode($journal->content ?? '[]', true);
    @endphp
    
    @foreach($sections as $idx => $sec)
        <div class="section-title">{{ $idx + 1 }}. {{ strtoupper($sec['title'] ?? '') }}</div>
        <div class="section-content">{{ $sec['content'] ?? '' }}</div>
    @endforeach

</body>
</html>
