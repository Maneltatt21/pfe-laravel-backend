<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Vehicle Management API",
    description: "A comprehensive API for managing vehicles, documents, maintenance records, and vehicle exchanges with role-based access control.",
    contact: new OA\Contact(
        name: "API Support",
        email: "support@vehiclemanagement.com"
    )
)]
#[OA\Server(
    url: "http://127.0.0.1:8000/api/v1",
    description: "Local development server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Enter your bearer token in the format: Bearer {token}"
)]
#[OA\Tag(
    name: "Authentication",
    description: "User authentication and authorization endpoints"
)]
#[OA\Tag(
    name: "Vehicles",
    description: "Vehicle management operations"
)]
#[OA\Tag(
    name: "Documents",
    description: "Vehicle document management"
)]
#[OA\Tag(
    name: "Maintenance",
    description: "Vehicle maintenance records"
)]
#[OA\Tag(
    name: "Exchanges",
    description: "Vehicle exchange requests"
)]
#[OA\Tag(
    name: "Users",
    description: "User management (Admin only)"
)]
abstract class Controller
{
    //
}
