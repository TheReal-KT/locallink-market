param(
    [string]$Source = "C:\Users\khulu\OneDrive - Eduvos\2026\E-Commerce",
    [string]$Target = "C:\xampp\htdocs\locallink-market"
)

$resolvedSource = (Resolve-Path -LiteralPath $Source).Path

if (-not (Test-Path -LiteralPath $Target)) {
    New-Item -ItemType Directory -Path $Target -Force | Out-Null
}

robocopy $resolvedSource $Target /E /R:1 /W:1 /XD .git | Out-Null

if ($LASTEXITCODE -gt 7) {
    throw "Robocopy failed with exit code $LASTEXITCODE."
}

Write-Output "Synced to $Target"
Write-Output "Open http://localhost/locallink-market/"
