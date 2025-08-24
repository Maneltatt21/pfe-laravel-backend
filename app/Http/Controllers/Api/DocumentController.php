<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: "/vehicles/{vehicle_id}/documents",
        summary: "Get vehicle documents",
        description: "Retrieve all documents for a specific vehicle",
        security: [["bearerAuth" => []]],
        tags: ["Documents"],
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
                description: "Documents retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/VehicleDocument")
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
        $documents = $vehicle->documents()->paginate(15);

        return response()->json($documents);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: "/vehicles/{vehicle_id}/documents",
        summary: "Upload vehicle document",
        description: "Upload a new document for a vehicle",
        security: [["bearerAuth" => []]],
        tags: ["Documents"],
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
                    required: ["type", "expiration_date", "file"],
                    properties: [
                        new OA\Property(
                            property: "type",
                            type: "string",
                            enum: ["carte_grise", "assurance", "controle_technique"],
                            example: "assurance"
                        ),
                        new OA\Property(
                            property: "expiration_date",
                            type: "string",
                            format: "date",
                            example: "2024-12-31"
                        ),
                        new OA\Property(
                            property: "file",
                            type: "string",
                            format: "binary",
                            description: "Document file (PDF, JPG, JPEG, PNG, max 2MB)"
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Document uploaded successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Document created successfully"),
                        new OA\Property(property: "document", ref: "#/components/schemas/VehicleDocument")
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
            'type' => 'required|in:carte_grise,assurance,controle_technique',
            'expiration_date' => 'required|date|after:today',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('documents', 'public');
        }

        $document = $vehicle->documents()->create([
            'type' => $request->type,
            'expiration_date' => $request->expiration_date,
            'file_path' => $filePath,
        ]);

        return response()->json([
            'message' => 'Document created successfully',
            'document' => $document,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: "/vehicles/{vehicle_id}/documents/{document_id}",
        summary: "Get document details",
        description: "Retrieve details of a specific vehicle document",
        security: [["bearerAuth" => []]],
        tags: ["Documents"],
        parameters: [
            new OA\Parameter(
                name: "vehicle_id",
                in: "path",
                description: "Vehicle ID",
                required: true,
                schema: new OA\Schema(type: "integer")
            ),
            new OA\Parameter(
                name: "document_id",
                in: "path",
                description: "Document ID",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Document details retrieved",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "document", ref: "#/components/schemas/VehicleDocument")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 404, description: "Document not found")
        ]
    )]
    public function show(Vehicle $vehicle, VehicleDocument $document): JsonResponse
    {
        // Ensure the document belongs to the vehicle
        if ($document->vehicle_id !== $vehicle->id) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        return response()->json([
            'document' => $document,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle, VehicleDocument $document): JsonResponse
    {
        // Ensure the document belongs to the vehicle
        if ($document->vehicle_id !== $vehicle->id) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        $request->validate([
            'type' => 'required|in:carte_grise,assurance,controle_technique',
            'expiration_date' => 'required|date|after:today',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $filePath = $document->file_path;
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $request->file('file')->store('documents', 'public');
        }

        $document->update([
            'type' => $request->type,
            'expiration_date' => $request->expiration_date,
            'file_path' => $filePath,
        ]);

        return response()->json([
            'message' => 'Document updated successfully',
            'document' => $document,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle, VehicleDocument $document): JsonResponse
    {
        // Ensure the document belongs to the vehicle
        if ($document->vehicle_id !== $vehicle->id) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        // Delete file if exists
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return response()->json([
            'message' => 'Document deleted successfully',
        ]);
    }

    /**
     * Get documents expiring soon.
     */
    #[OA\Get(
        path: "/vehicles/{vehicle_id}/documents/expiring",
        summary: "Get expiring documents",
        description: "Get documents that are expiring soon for a specific vehicle",
        security: [["bearerAuth" => []]],
        tags: ["Documents"],
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
                description: "Number of days to check for expiring documents",
                required: false,
                schema: new OA\Schema(type: "integer", default: 30)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Expiring documents retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "documents",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/VehicleDocument")
                        ),
                        new OA\Property(property: "count", type: "integer", example: 2)
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 404, description: "Vehicle not found")
        ]
    )]
    public function expiring(Vehicle $vehicle, Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'nullable|integer|min:1|max:365',
        ]);

        $days = (int) $request->get('days', 30);
        $documents = $vehicle->documents()->expiringSoon($days)->get();

        return response()->json([
            'documents' => $documents,
            'count' => $documents->count(),
        ]);
    }
}
