<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $check->document->original_filename }}</title>
    <style>
        @page { margin: 16mm 14mm; }
        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #1f2937;
            font-size: 11px;
            line-height: 1.65;
        }
        .heading {
            border-bottom: 1px solid #d1d5db;
            color: #374151;
            font-size: 16px;
            margin-bottom: 18px;
            padding-bottom: 8px;
        }
        .document-content {
            overflow-wrap: break-word;
            white-space: normal;
        }
        .document-content span {
            border-radius: 2px;
            padding: 0 2px;
        }
    </style>
</head>

<body>
    <div class="heading">{{ $check->document->original_filename }}</div>
    <div class="document-content">{!! $highlightedText !!}</div>
</body>

</html>