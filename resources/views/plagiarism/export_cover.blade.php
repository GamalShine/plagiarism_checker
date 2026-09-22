<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Originality Report - {{ $check->document->title }}</title>
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 48px 36px;
            text-align: center;
        }

        .logo {
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 28px;
        }

        .title {
            font-family: 'Lucida Sans Unicode', 'Lucida Sans', 'Lucida Grande', sans-serif;
            font-size: 36px;
            font-weight: normal !important;
            color: #111827;
            margin: 0;
            line-height: 1.35;
        }

        .subtitle {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 32px;
        }

        .score-box {
            display: inline-block;
            border: 2px solid
                {{ $check->similarity_color }}
            ;
            border-radius: 8px;
            padding: 20px 40px;
            margin-bottom: 32px;
        }

        .score-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .score-value {
            font-size: 44px;
            font-weight: 800;
            color:
                {{ $check->similarity_color }}
            ;
            line-height: 1;
        }

        .meta {
            width: 100%;
            max-width: 520px;
            margin: 0 auto;
            border-collapse: collapse;
            text-align: left;
            font-size: 12px;
        }

        .meta td {
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .meta td:first-child {
            width: 38%;
            color: #6b7280;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div style="text-align: center; padding-top: 120px;">
        <div class="title" style="font-family: 'Lucida Sans Unicode', 'Lucida Sans', sans-serif; font-size: 24px; font-weight: normal !important; margin-bottom: 24px;">{{ $check->document->title }}</div>

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
</body>

</html>