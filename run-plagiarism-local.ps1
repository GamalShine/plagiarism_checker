$ErrorActionPreference = 'Continue'

$projectPath = 'C:\laragon\www\plagiarism_checker'
$phpPath = 'C:\laragon\bin\php\php-8.3.9-Win32-vs16-x64\php.exe'
$logPath = Join-Path $projectPath 'storage\logs\queue-local.log'

Add-Content -Path $logPath -Value "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') Cron started"

Set-Location $projectPath
& $phpPath artisan queue:work database --queue=plagiarism --once --tries=1 --timeout=300 --verbose *>> $logPath

Add-Content -Path $logPath -Value "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') Cron finished with exit code $LASTEXITCODE"
