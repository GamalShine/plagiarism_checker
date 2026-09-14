<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $journal->title }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 40px;
        }
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .font-bold { font-weight: bold; }
        .italic { font-style: italic; }
        .title {
            font-size: 20pt;
            font-weight: bold;
            color: #1a1a2e;
            margin-bottom: 20px;
            text-align: left;
            border-bottom: 3px solid #4a4a8a;
            padding-bottom: 10px;
        }
        .author-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #4a4a8a;
            margin-bottom: 30px;
        }
        .author {
            font-size: 12pt;
            font-weight: bold;
            color: #4a4a8a;
            margin-bottom: 5px;
        }
        .institution {
            font-size: 10pt;
            color: #666;
            margin-bottom: 2px;
        }
        .email {
            font-size: 10pt;
            color: #666;
        }
        .abstract-box {
            margin-bottom: 30px;
        }
        .abstract-title {
            font-size: 13pt;
            font-weight: bold;
            color: #4a4a8a;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .abstract-content {
            font-size: 11pt;
            text-align: justify;
            margin-bottom: 15px;
        }
        .keywords {
            font-size: 10pt;
            background-color: #eef2f7;
            padding: 8px 12px;
            border-radius: 4px;
            display: inline-block;
        }
        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: #4a4a8a;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        .section-content {
            font-size: 11pt;
            text-align: justify;
            margin-bottom: 15px;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>

    <div class="title">{{ $journal->title }}</div>
    
    <div class="author-box">
        <div class="author">{{ $journal->author }}</div>
        @if($journal->institution)
            <div class="institution">{{ $journal->institution }}</div>
        @endif
        @if($journal->email)
            <div class="email">{{ $journal->email }}</div>
        @endif
    </div>

    <div class="abstract-box">
        <div class="abstract-title">Abstract</div>
        <div class="abstract-content">{{ $journal->abstract }}</div>
        <div class="keywords"><span class="font-bold">Keywords:</span> {{ $journal->keywords }}</div>
    </div>

    @php
        $sections = is_array($journal->content) ? $journal->content : json_decode($journal->content ?? '[]', true);
    @endphp
    
    @foreach($sections as $idx => $sec)
        <div class="section-title">{{ $idx + 1 }}. {{ $sec['title'] ?? '' }}</div>
        <div class="section-content">{{ $sec['content'] ?? '' }}</div>
    @endforeach

</body>
</html>
