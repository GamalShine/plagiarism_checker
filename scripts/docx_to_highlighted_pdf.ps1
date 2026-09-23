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
        return $null
    }

    $red = [Convert]::ToInt32($hex.Substring(0, 2), 16)
    $green = [Convert]::ToInt32($hex.Substring(2, 2), 16)
    $blue = [Convert]::ToInt32($hex.Substring(4, 2), 16)

    # Word shading has no opacity; blend toward white for a pastel marker effect.
    $red = [Math]::Round($red + ((255 - $red) * 0.55))
    $green = [Math]::Round($green + ((255 - $green) * 0.55))
    $blue = [Math]::Round($blue + ((255 - $blue) * 0.55))

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

        # Match the browser preview with Word-safe chunks. Word rejects long
        # Find.Text values, so split long sentences at word boundaries.
        $words = @($needle -split ' ' | Where-Object { $_.Length -gt 2 })
        $searchPhrases = @()
        $chunk = ''
        foreach ($wordPart in ($needle -split ' ')) {
            $candidate = if ($chunk) { "$chunk $wordPart" } else { $wordPart }
            if ($candidate.Length -gt 180 -and $chunk) {
                $searchPhrases += $chunk
                $chunk = $wordPart
            } else {
                $chunk = $candidate
            }
        }
        if ($chunk) {
            $searchPhrases += $chunk
        }

        if ($words.Count -ge 4) {
            $searchPhrases += (($words | Select-Object -First 6) -join ' ')
            if ($words.Count -ge 10) {
                $searchPhrases += (($words | Select-Object -Skip 4 -First 6) -join ' ')
            }
        }

        foreach ($phrase in ($searchPhrases | Select-Object -Unique)) {
            if ([string]::IsNullOrWhiteSpace($phrase) -or $phrase.Length -lt 6) {
                continue
            }

            $color = Convert-HexToWordColor ([string]$item.color)
            if ($null -eq $color) {
                continue
            }

            $search = $document.Content.Duplicate
            $find = $search.Find
            $find.ClearFormatting()
            $find.Text = $phrase
            $find.Forward = $true
            $find.Wrap = 0
            $find.Format = $false

            while ($find.Execute()) {
                $search.Shading.BackgroundPatternColor = $color
                $search.Collapse(0)
                $find = $search.Find
                $find.ClearFormatting()
                $find.Text = $phrase
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
