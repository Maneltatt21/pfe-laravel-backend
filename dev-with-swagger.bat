@echo off
echo Starting Laravel development environment with automatic Swagger generation...
echo.

REM Generate initial Swagger documentation
echo Generating initial Swagger documentation...
php artisan l5-swagger:generate
if %errorlevel% neq 0 (
    echo Failed to generate Swagger documentation
    pause
    exit /b 1
)

echo.
echo ✅ Swagger documentation generated successfully!
echo 📖 Documentation will be available at: http://localhost:8000/api/documentation
echo.

REM Start all development services with Swagger auto-generation
echo Starting development services...
npx concurrently -c "#93c5fd,#c4b5fd,#fdba74,#86efac" ^
    "php artisan serve" ^
    "php artisan queue:listen --tries=1" ^
    "npm run dev" ^
    "php artisan swagger:auto-generate --watch" ^
    --names="server,queue,vite,swagger"
