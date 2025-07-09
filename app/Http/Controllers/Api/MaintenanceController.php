<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class MaintenanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: "/vehicles/{vehicle_id}/maintenances",
        summary: "Get vehicle maintenance records",
        description: "Retrieve all maintenance records for a specific vehicle",
        security: [["bearerAuth" => []]],
        tags: ["Maintenance"],
        parameters: [
            new OA\Parameter(
                name: "vehicle_id",
                in: "path",
                description: "Vehicle ID",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Maintenance records retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/Maintenance")
                        ),
                        new OA\Property(property: "current_page", type: "integer"),
                        new OA\Property(property: "last_page", type: "integer"),
                        new OA\Property(property: "per_page", type: "integer"),
                        new OA\Property(property: "total", type: "integer")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 404, description: "Vehicle not found")
        ]
    )]
    public function index(Vehicle $vehicle): JsonResponse
    {
        $maintenances = $vehicle->maintenances()
            ->orderBy('date', 'desc')
            ->paginate(15);

        return response()->json($maintenances);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: "/vehicles/{vehicle_id}/maintenances",
        summary: "Create maintenance record",
        description: "Create a new maintenance record for a vehicle",
        security: [["bearerAuth" => []]],
        tags: ["Maintenance"],
        parameters: [
            new OA\Parameter(
                name: "vehicle_id",
                in: "path",
                description: "Vehicle ID",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["maintenance_type", "description", "date"],
                    properties: [
                        new OA\Property(
                            property: "maintenance_type",
                            type: "string",
                            example: "Oil Change"
                        ),
                        new OA\Property(
                            property: "description",
                            type: "string",
                            example: "Regular oil change service"
                        ),
                        new OA\Property(
                            property: "date",
                            type: "string",
                            format: "date",
                            example: "2024-01-15"
                        ),
                        new OA\Property(
                            property: "reminder_date",
                            type: "string",
                            format: "date",
                            example: "2024-04-15",
                            nullable: true
                        ),
                        new OA\Property(
                            property: "invoice",
                            type: "string",
                            format: "binary",
                            description: "Invoice file (PDF, JPG, JPEG, PNG, max 2MB)"
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Maintenance record created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Maintenance record created successfully"),
                        new OA\Property(property: "maintenance", ref: "#/components/schemas/Maintenance")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 404, description: "Vehicle not found"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(Request $request, Vehicle $vehicle): JsonResponse
    {
        $request->validate([
            'maintenance_type' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'reminder_date' => 'nullable|date|after:date',
            'invoice' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $invoicePath = null;
        if ($request->hasFile('invoice')) {
            $invoicePath = $request->file('invoice')->store('invoices', 'public');
        }

        $maintenance = $vehicle->maintenances()->create([
            'maintenance_type' => $request->maintenance_type,
            'description' => $request->description,
            'date' => $request->date,
            'reminder_date' => $request->reminder_date,
            'invoice_path' => $invoicePath,
        ]);

        return response()->json([
            'message' => 'Maintenance record created successfully',
            'maintenance' => $maintenance,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle, Maintenance $maintenance): JsonResponse
    {
        // Ensure the maintenance belongs to the vehicle
        if ($maintenance->vehicle_id !== $vehicle->id) {
            return response()->json(['message' => 'Maintenance record not found'], 404);
        }

        return response()->json([
            'maintenance' => $maintenance,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle, Maintenance $maintenance): JsonResponse
    {
        // Ensure the maintenance belongs to the vehicle
        if ($maintenance->vehicle_id !== $vehicle->id) {
            return response()->json(['message' => 'Maintenance record not found'], 404);
        }

        $request->validate([
            'maintenance_type' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'reminder_date' => 'nullable|date|after:date',
            'invoice' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $invoicePath = $maintenance->invoice_path;
        if ($request->hasFile('invoice')) {
            // Delete old invoice if exists
            if ($invoicePath) {
                Storage::disk('public')->delete($invoicePath);
            }
            $invoicePath = $request->file('invoice')->store('invoices', 'public');
        }

        $maintenance->update([
            'maintenance_type' => $request->maintenance_type,
            'description' => $request->description,
            'date' => $request->date,
            'reminder_date' => $request->reminder_date,
            'invoice_path' => $invoicePath,
        ]);

        return response()->json([
            'message' => 'Maintenance record updated successfully',
            'maintenance' => $maintenance,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle, Maintenance $maintenance): JsonResponse
    {
        // Ensure the maintenance belongs to the vehicle
        if ($maintenance->vehicle_id !== $vehicle->id) {
            return response()->json(['message' => 'Maintenance record not found'], 404);
        }

        // Delete invoice file if exists
        if ($maintenance->invoice_path) {
            Storage::disk('public')->delete($maintenance->invoice_path);
        }

        $maintenance->delete();

        return response()->json([
            'message' => 'Maintenance record deleted successfully',
        ]);
    }

    /**
     * Get upcoming maintenance reminders.
     */
    #[OA\Get(
        path: "/vehicles/{vehicle_id}/maintenances/upcoming",
        summary: "Get upcoming maintenance",
        description: "Get upcoming maintenance reminders for a specific vehicle",
        security: [["bearerAuth" => []]],
        tags: ["Maintenance"],
        parameters: [
            new OA\Parameter(
                name: "vehicle_id",
                in: "path",
                description: "Vehicle ID",
                required: true,
                schema: new OA\Schema(type: "integer")
            ),
            new OA\Parameter(
                name: "days",
                in: "query",
                description: "Number of days to check for upcoming maintenance",
                required: false,
                schema: new OA\Schema(type: "integer", default: 7)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Upcoming maintenance retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "maintenances",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/Maintenance")
                        ),
                        new OA\Property(property: "count", type: "integer", example: 1)
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 404, description: "Vehicle not found")
        ]
    )]
    public function upcoming(Vehicle $vehicle, Request $request): JsonResponse
    {
        $days = $request->get('days', 7);
        $maintenances = $vehicle->maintenances()->upcoming($days)->get();

        return response()->json([
            'maintenances' => $maintenances,
            'count' => $maintenances->count(),
        ]);
    }
}
