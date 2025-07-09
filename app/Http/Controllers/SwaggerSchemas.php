<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

/**
 * Common Swagger schemas for the API
 */
class SwaggerSchemas
{
    // This class is just for organizing schemas
}

#[OA\Schema(
    schema: "ErrorResponse",
    title: "Error Response",
    description: "Standard error response format",
    properties: [
        new OA\Property(property: "message", type: "string", example: "The given data was invalid."),
        new OA\Property(
            property: "errors",
            type: "object",
            example: [
                "email" => ["The email field is required."],
                "password" => ["The password field is required."]
            ]
        )
    ]
)]
class ErrorResponse {}

#[OA\Schema(
    schema: "SuccessResponse",
    title: "Success Response",
    description: "Standard success response format",
    properties: [
        new OA\Property(property: "message", type: "string", example: "Operation completed successfully"),
        new OA\Property(property: "data", type: "object", description: "Response data")
    ]
)]
class SuccessResponse {}

#[OA\Schema(
    schema: "PaginatedResponse",
    title: "Paginated Response",
    description: "Standard paginated response format",
    properties: [
        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object")),
        new OA\Property(property: "current_page", type: "integer", example: 1),
        new OA\Property(property: "first_page_url", type: "string", example: "http://example.com/api/v1/vehicles?page=1"),
        new OA\Property(property: "from", type: "integer", example: 1),
        new OA\Property(property: "last_page", type: "integer", example: 5),
        new OA\Property(property: "last_page_url", type: "string", example: "http://example.com/api/v1/vehicles?page=5"),
        new OA\Property(property: "links", type: "array", items: new OA\Items(type: "object")),
        new OA\Property(property: "next_page_url", type: "string", nullable: true, example: "http://example.com/api/v1/vehicles?page=2"),
        new OA\Property(property: "path", type: "string", example: "http://example.com/api/v1/vehicles"),
        new OA\Property(property: "per_page", type: "integer", example: 15),
        new OA\Property(property: "prev_page_url", type: "string", nullable: true),
        new OA\Property(property: "to", type: "integer", example: 15),
        new OA\Property(property: "total", type: "integer", example: 67)
    ]
)]
class PaginatedResponse {}

#[OA\Schema(
    schema: "VehicleDocument",
    title: "Vehicle Document",
    description: "Vehicle document model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "vehicle_id", type: "integer", example: 1),
        new OA\Property(property: "type", type: "string", enum: ["carte_grise", "assurance", "controle_technique"], example: "assurance"),
        new OA\Property(property: "expiration_date", type: "string", format: "date", example: "2024-12-31"),
        new OA\Property(property: "file_path", type: "string", nullable: true, example: "documents/doc_2024-01-01_abc123.pdf"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2024-01-01T00:00:00Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2024-01-01T00:00:00Z")
    ]
)]
class VehicleDocumentSchema {}

#[OA\Schema(
    schema: "Maintenance",
    title: "Maintenance",
    description: "Vehicle maintenance record model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "vehicle_id", type: "integer", example: 1),
        new OA\Property(property: "maintenance_type", type: "string", example: "Oil Change"),
        new OA\Property(property: "description", type: "string", example: "Regular oil change service"),
        new OA\Property(property: "date", type: "string", format: "date", example: "2024-01-15"),
        new OA\Property(property: "reminder_date", type: "string", format: "date", nullable: true, example: "2024-04-15"),
        new OA\Property(property: "invoice_path", type: "string", nullable: true, example: "invoices/inv_2024-01-15_xyz789.pdf"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2024-01-01T00:00:00Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2024-01-01T00:00:00Z")
    ]
)]
class MaintenanceSchema {}

#[OA\Schema(
    schema: "VehicleExchange",
    title: "Vehicle Exchange",
    description: "Vehicle exchange request model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "from_driver_id", type: "integer", example: 2),
        new OA\Property(property: "to_driver_id", type: "integer", example: 3),
        new OA\Property(property: "vehicle_id", type: "integer", example: 1),
        new OA\Property(property: "request_date", type: "string", format: "date-time", example: "2024-01-01T10:00:00Z"),
        new OA\Property(property: "status", type: "string", enum: ["pending", "approved", "rejected"], example: "pending"),
        new OA\Property(property: "before_photo_path", type: "string", nullable: true, example: "exchange_photos/photo_2024-01-01_abc123.jpg"),
        new OA\Property(property: "after_photo_path", type: "string", nullable: true, example: "exchange_photos/photo_2024-01-01_def456.jpg"),
        new OA\Property(property: "note", type: "string", nullable: true, example: "Vehicle needs maintenance"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2024-01-01T00:00:00Z"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2024-01-01T00:00:00Z")
    ]
)]
class VehicleExchangeSchema {}
