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
$appliedHighlightRanges = 0
$searchedPhraseCount = 0

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

    $highlightsJson = Get-Content -LiteralPath $HighlightsPath -Raw
    $highlights = ConvertFrom-Json -InputObject $highlightsJson
    $outputDirectory = Split-Path -Parent $OutputPath
    if ($outputDirectory -and -not (Test-Path -LiteralPath $outputDirectory)) {
        New-Item -ItemType Directory -Force -Path $outputDirectory | Out-Null
    }

    $word = New-Object -ComObject Word.Application
    $word.Visible = $false
    $word.DisplayAlerts = 0
    $document = $word.Documents.Open((Resolve-Path -LiteralPath $InputPath).Path, $false, $false)
    $searchedPhrases = @{}

    foreach ($item in $highlights) {
        $needle = [string]$item.text
        if ([string]::IsNullOrWhiteSpace($needle)) {
            continue
        }

        $searchPhrases = @()
        # Highlight records may span multiple DOCX paragraphs. Word Find cannot
        # match across paragraph marks, so search independently inside each line
        # and add overlapping short phrases for long paragraphs.
        foreach ($line in ($needle -split '[\r\n]+')) {
            $line = (($line -replace '\t+', ' ') -replace '\s+', ' ').Trim()
            if ($line.Length -lt 6) {
                continue
            }

            $lineWords = @($line -split ' ' | Where-Object { $_.Length -gt 0 })
            if ($line.Length -le 180) {
                $searchPhrases += $line
            }

            for ($start = 0; $start -lt $lineWords.Count; $start += 4) {
                $take = [Math]::Min(6, $lineWords.Count - $start)
                if ($take -lt 4) {
                    continue
                }

                $searchPhrases += (($lineWords | Select-Object -Skip $start -First $take) -join ' ')
            }
        }

        foreach ($phrase in ($searchPhrases | Select-Object -Unique)) {
            if ([string]::IsNullOrWhiteSpace($phrase) -or $phrase.Length -lt 6 -or $searchedPhrases.ContainsKey($phrase)) {
                continue
            }

            $color = Convert-HexToWordColor ([string]$item.color)
            if ($null -eq $color) {
                continue
            }
            $searchedPhrases[$phrase] = $true

            $search = $document.Content.Duplicate
            $find = $search.Find
            $searchedPhraseCount++
            $find.ClearFormatting()
            $find.Text = $phrase
            $find.Forward = $true
            $find.Wrap = 0
            $find.Format = $false

            while ($find.Execute()) {
                $search.Shading.BackgroundPatternColor = $color
                $appliedHighlightRanges++
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

    Write-Output "SEARCH_PHRASES=$searchedPhraseCount"
    Write-Output "HIGHLIGHTS_APPLIED=$appliedHighlightRanges"
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
