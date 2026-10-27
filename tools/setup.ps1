param([switch]$SkipBuild)
$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath (Split-Path -Parent $PSScriptRoot)
if (-not (Test-Path -LiteralPath '.env')) {
    $bytes = New-Object byte[] 32
    $rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()
    $rng.GetBytes($bytes)
    $secret = [System.BitConverter]::ToString($bytes).Replace('-', '').ToLowerInvariant()
    $content = [System.IO.File]::ReadAllText((Join-Path (Get-Location) '.env.example'))
    $content = $content.Replace('SESSION_SECRET=' + "`n", 'SESSION_SECRET=' + $secret + "`n").Replace('SESSION_SECRET=' + "`r`n", 'SESSION_SECRET=' + $secret + "`r`n")
    [System.IO.File]::WriteAllText((Join-Path (Get-Location) '.env'), $content, (New-Object System.Text.UTF8Encoding $false))
    Write-Host 'Created .env with a new random secret (not displayed).'
}
if (-not $SkipBuild) { docker compose build; if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE } }
docker compose run --rm app composer install --no-interaction --prefer-dist
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
docker compose run --rm app php database/seed.php
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
docker compose up -d
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
Write-Host 'Relay is ready at http://localhost:8080. Demo users: alice, bob, charlie. Password: ChatDemo!2026'
