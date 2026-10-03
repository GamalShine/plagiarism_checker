<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        {!! $documentStyles ?? '' !!}
    </style>
    <style>
        /* Keep these page margins after imported DOCX CSS so Dompdf applies them. */
        @page { margin: 1.58in 1.18in 1.18in 1.58in; }
        /* Do not reset body margin here: Dompdf uses it when calculating @page margins. */
        html, body { color: #111827; font-family: 'Times New Roman', Times, serif; font-size: 11px; line-height: 1.55; padding: 0; }
        .document-content { font-family: 'Times New Roman', Times, serif !important; overflow-wrap: anywhere; }
        .document-content * { font-family: 'Times New Roman', Times, serif !important; }
        .document-content p { margin-left: 0 !important; margin-right: 0 !important; }
        .document-content .doc-paragraph { margin: 0 0 10px; text-align: justify; }
        .document-content .doc-paragraph:last-child { margin-bottom: 0; }
        .document-content .doc-heading { color: #111827; font-weight: 700; margin: 16px 0 10px; text-transform: none; }
        .document-content .doc-heading-1 { margin-left: 0; margin-right: 0; text-align: center; text-transform: uppercase; }
        .document-content .doc-heading-2 { margin-left: 0; margin-right: 0; text-align: left; }
        .document-content .doc-heading-3 { margin-left: 0.5in; margin-right: 0; text-align: left; }
        .document-content mark.t-highlight { border-radius: 1px; color: #111827; padding: 0 1px; }
        .document-content .t-badge { color: #ffffff; display: inline-block; font-family: Arial, Helvetica, sans-serif !important; font-size: 8px; font-weight: 700; line-height: 1; margin: 0 2px 0 0; padding: 1px 3px; vertical-align: super; white-space: nowrap; }
        .document-content .t-badge-main { margin-left: 1em; }
        .document-content .t-badge-sub { margin-left: 2em; }
    </style>
</head>
<body>
    <div class="document-content">{!! $documentHtml !!}</div>
</body>
</html>
