param(
    [Parameter(Mandatory = $true)][ValidateRange(1, 3)][int]$Round,
    [Parameter(Mandatory = $true)][string]$Repo,
    [switch]$Preview
)
$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

# This script is copied into each package beside package.json and dot-XX/.
$package = Get-Content -LiteralPath (Join-Path $PSScriptRoot 'package.json') -Raw -Encoding UTF8 | ConvertFrom-Json
$roundPath = Join-Path $PSScriptRoot ('dot-{0:00}' -f $Round)
$manifest = Get-Content -LiteralPath (Join-Path $roundPath 'manifest.json') -Raw -Encoding UTF8 | ConvertFrom-Json
$repoPath = (Resolve-Path -LiteralPath $Repo).Path
$gitRoot = & git -C $repoPath rev-parse --show-toplevel 2>$null
if ($LASTEXITCODE -ne 0) { throw 'Repo must be an existing Git clone. See HUONG_DAN_CHUNG.md.' }
if ([IO.Path]::GetFullPath($gitRoot.Trim()) -ne [IO.Path]::GetFullPath($repoPath)) {
    throw 'Repo must point to the Git repository root.'
}
$pending = & git -C $repoPath status --porcelain --untracked-files=all
if ($LASTEXITCODE -ne 0) { throw 'Cannot inspect Git status.' }
if ($pending) { throw 'Working tree is not clean. Commit or move your own pending work before importing.' }

$copies = @()
foreach ($entry in $manifest.files) {
    $relative = [string]$entry.path
    if ([IO.Path]::IsPathRooted($relative) -or $relative -match '(^|[\\/])\.\.([\\/]|$)' -or $relative.Contains(':')) {
        throw "Invalid manifest path: $relative"
    }
    $source = Join-Path (Join-Path $roundPath 'files') $relative
    $target = Join-Path $repoPath $relative
    if (-not (Test-Path -LiteralPath $source -PathType Leaf)) { throw "Missing payload: $relative" }
    $sourceHash = (Get-FileHash -LiteralPath $source -Algorithm SHA256).Hash.ToLowerInvariant()
    if ($sourceHash -ne $entry.sha256) { throw "Payload changed since packaging: $relative" }
    if (Test-Path -LiteralPath $target) {
        if (-not (Test-Path -LiteralPath $target -PathType Leaf)) { throw "Target is not a file: $relative" }
        $targetHash = (Get-FileHash -LiteralPath $target -Algorithm SHA256).Hash.ToLowerInvariant()
        if ($targetHash -ne $sourceHash) { throw "Different file already exists; merge it manually: $relative" }
        Write-Host "Already present: $relative"
    } else {
        $copies += [PSCustomObject]@{ Source = $source; Target = $target; Path = $relative }
    }
}

Write-Host ("Member {0}, round {1}: {2} new files" -f $package.member, $Round, $copies.Count)
if ($Preview) {
    $copies | ForEach-Object { Write-Host $_.Path }
    Write-Host 'Preview only. No files copied.'
    exit 0
}
foreach ($copy in $copies) {
    $parent = Split-Path -Parent $copy.Target
    [void][IO.Directory]::CreateDirectory($parent)
    Copy-Item -LiteralPath $copy.Source -Destination $copy.Target
}
Write-Host 'Imported. Review the files and checks before creating your own commit.'
& git -C $repoPath status --short
if ($LASTEXITCODE -ne 0) { throw 'Cannot inspect imported changes.' }
