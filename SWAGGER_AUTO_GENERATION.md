# Automatic Swagger Documentation Generation

This Laravel backend is configured to automatically generate and update Swagger API documentation during development.

## 🚀 Quick Start

### Option 1: Using Composer (Recommended)
```bash
composer run dev
```
This will:
- Generate initial Swagger documentation
- Start Laravel server (http://localhost:8000)
- Start queue worker
- Start Vite for asset compilation

### Option 2: Using NPM with Auto-Swagger
```bash
npm run dev:swagger
```
This will do everything from Option 1 plus:
- Automatically watch for file changes
- Regenerate Swagger docs when API files are modified

### Option 3: Using Scripts
**Windows Batch:**
```bash
./dev-with-swagger.bat
```

**PowerShell:**
```bash
./dev-with-swagger.ps1
```

## 📖 Accessing Documentation

Once the server is running, access your API documentation at:
- **Swagger UI**: http://localhost:8000/api/documentation
- **JSON Format**: http://localhost:8000/docs
- **YAML Format**: http://localhost:8000/docs (if enabled)

## 🔧 Manual Commands

### Generate Documentation Once
```bash
php artisan l5-swagger:generate
```

### Auto-Generate with File Watching
```bash
php artisan swagger:auto-generate --watch
```

## ⚙️ Configuration

### Environment Variables
Add these to your `.env` file for customization:

```env
# Auto-generate docs on each request (development only)
L5_SWAGGER_GENERATE_ALWAYS=true

# Generate YAML copy
L5_SWAGGER_GENERATE_YAML_COPY=false

# API documentation format
L5_FORMAT_TO_USE_FOR_DOCS=json

# Base path for API
L5_SWAGGER_BASE_PATH=null

# UI Configuration
L5_SWAGGER_UI_DARK_MODE=false
L5_SWAGGER_UI_DOC_EXPANSION=none
L5_SWAGGER_UI_FILTERS=true
```

### File Watching
The auto-generation watches these directories for changes:
- `app/Http/Controllers/`
- `app/Models/`
- `routes/api.php`

## 📝 Adding API Documentation

Use OpenAPI 3.0 attributes in your controllers:

```php
#[OA\Get(
    path: "/api/v1/vehicles",
    summary: "Get all vehicles",
    tags: ["Vehicles"],
    security: [["bearerAuth" => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: "List of vehicles",
            content: new OA\JsonContent(
                type: "array",
                items: new OA\Items(ref: "#/components/schemas/Vehicle")
            )
        )
    ]
)]
public function index()
{
    // Your controller logic
}
```

## 🔍 Troubleshooting

### Documentation Not Updating
1. Check if `L5_SWAGGER_GENERATE_ALWAYS=true` in `.env`
2. Manually regenerate: `php artisan l5-swagger:generate`
3. Clear cache: `php artisan config:clear`

### Permission Issues
Ensure the `storage/api-docs` directory is writable:
```bash
chmod -R 775 storage/api-docs
```

### Missing Dependencies
If you get errors, ensure L5-Swagger is installed:
```bash
composer require darkaonline/l5-swagger
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
```

## 📚 Resources

- [L5-Swagger Documentation](https://github.com/DarkaOnLine/L5-Swagger)
- [OpenAPI 3.0 Specification](https://swagger.io/specification/)
- [Swagger UI Documentation](https://swagger.io/tools/swagger-ui/)
