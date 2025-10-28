#!/usr/bin/env pwsh

Write-Host "🔍 Buscando credenciales hardcodeadas en el proyecto..." -ForegroundColor Cyan
Write-Host ""

$patterns = @(
    '3\.149\.97\.214',
    'depositos_user',
    'password.*=.*["'']admin["'']',
    'mysql:host=.*3\.149'
)

$exclude = @('.env', 'node_modules', '.git', 'vendor')

$found = $false

foreach ($pattern in $patterns) {
    Write-Host "Patrón: $pattern" -ForegroundColor Yellow
    
    Get-ChildItem -Path "d:\xampp\htdocs\prof" -Recurse -File -Include *.php,*.js,*.html,*.md |
        Where-Object { 
            $exclude | ForEach-Object { $_.FullName -notlike "*$_*" }
        } |
        Select-String -Pattern $pattern |
        ForEach-Object {
            $found = $true
            Write-Host "  ❌ $($_.Path):$($_.LineNumber)" -ForegroundColor Red
            Write-Host "     $($_.Line.Trim())" -ForegroundColor Gray
        }
}

Write-Host ""
if ($found) {
    Write-Host "⚠️  Se encontraron credenciales en el código" -ForegroundColor Red
} else {
    Write-Host "✅ No se encontraron credenciales hardcodeadas" -ForegroundColor Green
}
