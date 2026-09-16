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

function Convert-HexToWordColor([string]$HexColor) {
    $hex = ($HexColor -replace '#', '').Trim()
    if ($hex.Length -ne 6 -or $hex -notmatch '^[0-9a-fA-F]{6}$') {
        return 65535
    }

    $red = [Convert]::ToInt32($hex.Substring(0, 2), 16)
    $green = [Convert]::ToInt32($hex.Substring(2, 2), 16)
    $blue = [Convert]::ToInt32($hex.Substring(4, 2), 16)

    return $red + ($green * 256) + ($blue * 65536)
}

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

        $needle = (($needle -replace '[\r\n\t]+', ' ') -replace '\s+', ' ').Trim()
        if ($needle.Length -eq 0) {
            continue
        }

        # Word Find.Text rejects long strings. Search in word-boundary chunks
        # so long detected sentences still receive a continuous highlight.
        $words = $needle -split ' '
        $chunks = @()
        $chunk = ''
        foreach ($word in $words) {
            $candidate = if ($chunk) { "$chunk $word" } else { $word }
            if ($candidate.Length -gt 180 -and $chunk) {
                $chunks += $chunk
                $chunk = $word
            } else {
                $chunk = $candidate
            }
        }
        if ($chunk) {
            $chunks += $chunk
        }

        foreach ($chunk in $chunks) {
            $search = $document.Content.Duplicate
            $find = $search.Find
            $find.ClearFormatting()
            $find.Text = $chunk
            $find.Forward = $true
            $find.Wrap = 0
            $find.Format = $false

            while ($find.Execute()) {
                $color = Convert-HexToWordColor ([string]$item.color)
                $search.Shading.BackgroundPatternColor = $color
                $search.Collapse(0)
                $find = $search.Find
                $find.ClearFormatting()
                $find.Text = $chunk
                $find.Forward = $true
                $find.Wrap = 0
                $find.Format = $false
            }
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
