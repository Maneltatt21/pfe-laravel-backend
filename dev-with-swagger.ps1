#!/usr/bin/env pwsh

Write-Host "🚀 Starting Laravel development environment with automatic Swagger generation..." -ForegroundColor Green
Write-Host ""

# Generate initial Swagger documentation
Write-Host "📝 Generating initial Swagger documentation..." -ForegroundColor Yellow
try {
    php artisan l5-swagger:generate
    Write-Host "✅ Swagger documentation generated successfully!" -ForegroundColor Green
    Write-Host "📖 Documentation will be available at: http://localhost:8000/api/documentation" -ForegroundColor Cyan
    Write-Host ""
} catch {
    Write-Host "❌ Failed to generate Swagger documentation" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}

# Start all development services with Swagger auto-generation
Write-Host "🔧 Starting development services..." -ForegroundColor Yellow
Write-Host "Services: Laravel Server, Queue Worker, Vite, Swagger Auto-Generator" -ForegroundColor Gray
Write-Host ""

npx concurrently -c "#93c5fd,#c4b5fd,#fdba74,#86efac" `
    "php artisan serve" `
    "php artisan queue:listen --tries=1" `
    "npm run dev" `
    "php artisan swagger:auto-generate --watch" `
    --names="server,queue,vite,swagger"
