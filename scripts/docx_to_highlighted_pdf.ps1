param(
    [Parameter(Mandatory = $true)]
    [string]$InputPath,

    [Parameter(Mandatory = $true)]
    [string]$OutputPath,

    [Parameter(Mandatory = $true)]
    [string]$HighlightsPath
)

$ErrorActionPreference = 'Stop'
$word = $null
$document = $null

try {
    if (-not (Test-Path -LiteralPath $InputPath)) {
        throw "Input file not found: $InputPath"
    }

    $highlights = @(Get-Content -LiteralPath $HighlightsPath -Raw | ConvertFrom-Json)
    $outputDirectory = Split-Path -Parent $OutputPath
    if ($outputDirectory -and -not (Test-Path -LiteralPath $outputDirectory)) {
        New-Item -ItemType Directory -Force -Path $outputDirectory | Out-Null
    }

    $word = New-Object -ComObject Word.Application
    $word.Visible = $false
    $word.DisplayAlerts = 0
    $document = $word.Documents.Open((Resolve-Path -LiteralPath $InputPath).Path, $false, $false)

    foreach ($item in $highlights) {
        $needle = [string]$item.text
        if ([string]::IsNullOrWhiteSpace($needle)) {
            continue
        }

        $needle = $needle.Trim()
        if ($needle.Length -gt 250) {
            $needle = $needle.Substring(0, 250)
        }

        $search = $document.Content.Duplicate
        $find = $search.Find
        $find.ClearFormatting()
        $find.Text = $needle
        $find.Forward = $true
        $find.Wrap = 0
        $find.Format = $false

        while ($find.Execute()) {
            $search.HighlightColorIndex = 7
            $search.Collapse(0)
            $find = $search.Find
            $find.ClearFormatting()
            $find.Text = $needle
            $find.Forward = $true
            $find.Wrap = 0
            $find.Format = $false
        }
    }

    $document.ExportAsFixedFormat($OutputPath, 17)
    $document.Close($false)
    $document = $null

    if (-not (Test-Path -LiteralPath $OutputPath) -or ((Get-Item -LiteralPath $OutputPath).Length -le 0)) {
        throw 'Word did not produce a highlighted PDF file.'
    }

    Write-Output 'OK'
}
catch {
    if ($document) { $document.Close($false) }
    Write-Error $_.Exception.Message
    exit 1
}
finally {
    if ($word) {
        $word.Quit()
        [System.Runtime.Interopservices.Marshal]::ReleaseComObject($word) | Out-Null
        [GC]::Collect()
        [GC]::WaitForPendingFinalizers()
    }
}
