<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: "/vehicles",
        summary: "Get all vehicles",
        description: "Retrieve a paginated list of vehicles with optional filtering",
        security: [["bearerAuth" => []]],
        tags: ["Vehicles"],
        parameters: [
            new OA\Parameter(
                name: "status",
                in: "query",
                description: "Filter by vehicle status",
                required: false,
                schema: new OA\Schema(type: "string", enum: ["active", "archived"])
            ),
            new OA\Parameter(
                name: "search",
                in: "query",
                description: "Search by registration number or model",
                required: false,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "page",
                in: "query",
                description: "Page number for pagination",
                required: false,
                schema: new OA\Schema(type: "integer", minimum: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Vehicles retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/Vehicle")
                        ),
                        new OA\Property(property: "current_page", type: "integer"),
                        new OA\Property(property: "last_page", type: "integer"),
                        new OA\Property(property: "per_page", type: "integer"),
                        new OA\Property(property: "total", type: "integer")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Vehicle::with(['assignedUser', 'documents', 'maintenances']);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by registration number or model
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            });
        }

        $vehicles = $query->paginate(15);

        return response()->json($vehicles);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: "/vehicles",
        summary: "Create a new vehicle",
        description: "Create a new vehicle (Admin only)",
        security: [["bearerAuth" => []]],
        tags: ["Vehicles"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["registration_number", "model", "year"],
                properties: [
                    new OA\Property(property: "registration_number", type: "string", example: "ABC-123"),
                    new OA\Property(property: "model", type: "string", example: "Toyota Camry"),
                    new OA\Property(property: "year", type: "integer", minimum: 1900, example: 2022)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Vehicle created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Vehicle created successfully"),
                        new OA\Property(property: "vehicle", ref: "#/components/schemas/Vehicle")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Admin only"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'registration_number' => 'required|string|unique:vehicles',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
        ]);

        $vehicle = Vehicle::create($request->only([
            'registration_number',
            'model',
            'year',
        ]));

        return response()->json([
            'message' => 'Vehicle created successfully',
            'vehicle' => $vehicle->load(['assignedUser', 'documents', 'maintenances']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: "/vehicles/{id}",
        summary: "Get vehicle details",
        description: "Retrieve detailed information about a specific vehicle",
        security: [["bearerAuth" => []]],
        tags: ["Vehicles"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "Vehicle ID",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Vehicle details retrieved",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "vehicle", ref: "#/components/schemas/Vehicle")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 404, description: "Vehicle not found")
        ]
    )]
    public function show(Vehicle $vehicle): JsonResponse
    {
        return response()->json([
            'vehicle' => $vehicle->load(['assignedUser', 'documents', 'maintenances', 'exchanges']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: "/vehicles/{id}",
        summary: "Update vehicle",
        description: "Update vehicle information (Admin only)",
        security: [["bearerAuth" => []]],
        tags: ["Vehicles"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "Vehicle ID",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["registration_number", "model", "year"],
                properties: [
                    new OA\Property(property: "registration_number", type: "string", example: "ABC-123"),
                    new OA\Property(property: "model", type: "string", example: "Toyota Camry"),
                    new OA\Property(property: "year", type: "integer", minimum: 1900, example: 2022)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Vehicle updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Vehicle updated successfully"),
                        new OA\Property(property: "vehicle", ref: "#/components/schemas/Vehicle")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Admin only"),
            new OA\Response(response: 404, description: "Vehicle not found"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $request->validate([
            'registration_number' => 'required|string|unique:vehicles,registration_number,' . $vehicle->id,
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
        ]);

        $vehicle->update($request->only([
            'registration_number',
            'model',
            'year',
        ]));

        return response()->json([
            'message' => 'Vehicle updated successfully',
            'vehicle' => $vehicle->load(['assignedUser', 'documents', 'maintenances']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: "/vehicles/{id}",
        summary: "Delete vehicle",
        description: "Archive a vehicle (Admin only)",
        security: [["bearerAuth" => []]],
        tags: ["Vehicles"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "Vehicle ID",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Vehicle archived successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Vehicle archived successfully")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Admin only"),
            new OA\Response(response: 404, description: "Vehicle not found")
        ]
    )]
    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $vehicle->archive();

        return response()->json([
            'message' => 'Vehicle archived successfully',
        ]);
    }

    /**
     * Archive a vehicle.
     */
    #[OA\Post(
        path: "/vehicles/{id}/archive",
        summary: "Archive vehicle",
        description: "Archive a specific vehicle (Admin only)",
        security: [["bearerAuth" => []]],
        tags: ["Vehicles"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "Vehicle ID",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Vehicle archived successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Vehicle archived successfully"),
                        new OA\Property(property: "vehicle", ref: "#/components/schemas/Vehicle")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Admin only"),
            new OA\Response(response: 404, description: "Vehicle not found")
        ]
    )]
    public function archive(Vehicle $vehicle): JsonResponse
    {
        $vehicle->archive();

        return response()->json([
            'message' => 'Vehicle archived successfully',
            'vehicle' => $vehicle->fresh(),
        ]);
    }

    /**
     * Restore an archived vehicle.
     */
    #[OA\Post(
        path: "/vehicles/{id}/restore",
        summary: "Restore archived vehicle",
        description: "Restore an archived vehicle (Admin only)",
        security: [["bearerAuth" => []]],
        tags: ["Vehicles"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "Vehicle ID",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Vehicle restored successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Vehicle restored successfully"),
                        new OA\Property(property: "vehicle", ref: "#/components/schemas/Vehicle")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Admin only"),
            new OA\Response(response: 404, description: "Vehicle not found")
        ]
    )]
    public function restore(Vehicle $vehicle): JsonResponse
    {
        $vehicle->restore();

        return response()->json([
            'message' => 'Vehicle restored successfully',
            'vehicle' => $vehicle->fresh(),
        ]);
    }

    /**
     * Get the vehicle assigned to the authenticated chauffeur.
     */
    #[OA\Get(
        path: "/my-vehicle",
        summary: "Get my assigned vehicle",
        description: "Get the vehicle assigned to the authenticated chauffeur",
        security: [["bearerAuth" => []]],
        tags: ["Vehicles"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Vehicle information retrieved",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "vehicle", ref: "#/components/schemas/Vehicle")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "No vehicle assigned",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "No vehicle assigned to you")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated")
        ]
    )]
    public function myVehicle(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->vehicle) {
            return response()->json([
                'message' => 'No vehicle assigned to you',
            ], 404);
        }

        return response()->json([
            'vehicle' => $user->vehicle->load(['documents', 'maintenances']),
        ]);
    }
}
