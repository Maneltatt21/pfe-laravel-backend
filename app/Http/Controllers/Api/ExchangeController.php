<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VehicleExchange;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class ExchangeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: "/exchanges",
        summary: "Get vehicle exchanges",
        description: "Retrieve all vehicle exchange requests (Admin sees all, Chauffeur sees only their own)",
        security: [["bearerAuth" => []]],
        tags: ["Exchanges"],
        parameters: [
            new OA\Parameter(
                name: "status",
                in: "query",
                description: "Filter by exchange status",
                required: false,
                schema: new OA\Schema(type: "string", enum: ["pending", "approved", "rejected"])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Exchange requests retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/VehicleExchange")
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
        $query = VehicleExchange::with(['fromDriver', 'toDriver', 'vehicle']);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // For chauffeurs, only show their exchanges
        if ($request->user()->isChauffeur()) {
            $query->where(function ($q) use ($request) {
                $q->where('from_driver_id', $request->user()->id)
                    ->orWhere('to_driver_id', $request->user()->id);
            });
        }

        $exchanges = $query->orderBy('request_date', 'desc')->paginate(15);

        return response()->json($exchanges);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: "/exchanges",
        summary: "Create vehicle exchange request",
        description: "Create a new vehicle exchange request (Chauffeur only)",
        security: [["bearerAuth" => []]],
        tags: ["Exchanges"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["to_driver_id", "vehicle_id"],
                    properties: [
                        new OA\Property(
                            property: "to_driver_id",
                            type: "integer",
                            example: 2,
                            description: "ID of the chauffeur to exchange with"
                        ),
                        new OA\Property(
                            property: "vehicle_id",
                            type: "integer",
                            example: 1,
                            description: "ID of the vehicle to exchange"
                        ),
                        new OA\Property(
                            property: "note",
                            type: "string",
                            example: "Need to exchange for maintenance",
                            nullable: true
                        ),
                        new OA\Property(
                            property: "before_photo",
                            type: "string",
                            format: "binary",
                            description: "Photo before exchange (JPG, JPEG, PNG, max 2MB)"
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Exchange request created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Vehicle exchange request created successfully"),
                        new OA\Property(property: "exchange", ref: "#/components/schemas/VehicleExchange")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Chauffeur only"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'to_driver_id' => 'required|exists:users,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'note' => 'nullable|string',
            'before_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Ensure the to_driver is a chauffeur
        $toDriver = User::find($request->to_driver_id);
        if (!$toDriver->isChauffeur()) {
            return response()->json(['message' => 'Target user must be a chauffeur'], 422);
        }

        $beforePhotoPath = null;
        if ($request->hasFile('before_photo')) {
            $beforePhotoPath = $request->file('before_photo')->store('exchange_photos', 'public');
        }

        $exchange = VehicleExchange::create([
            'from_driver_id' => $request->user()->id,
            'to_driver_id' => $request->to_driver_id,
            'vehicle_id' => $request->vehicle_id,
            'request_date' => now(),
            'note' => $request->note,
            'before_photo_path' => $beforePhotoPath,
        ]);

        return response()->json([
            'message' => 'Vehicle exchange request created successfully',
            'exchange' => $exchange->load(['fromDriver', 'toDriver', 'vehicle']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleExchange $exchange): JsonResponse
    {
        return response()->json([
            'exchange' => $exchange->load(['fromDriver', 'toDriver', 'vehicle']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleExchange $exchange): JsonResponse
    {
        // Only allow updating if exchange is pending and user is the initiator
        if ($exchange->status !== 'pending' || $exchange->from_driver_id !== $request->user()->id) {
            return response()->json(['message' => 'Cannot update this exchange'], 403);
        }

        $request->validate([
            'note' => 'nullable|string',
            'after_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $afterPhotoPath = $exchange->after_photo_path;
        if ($request->hasFile('after_photo')) {
            // Delete old photo if exists
            if ($afterPhotoPath) {
                Storage::disk('public')->delete($afterPhotoPath);
            }
            $afterPhotoPath = $request->file('after_photo')->store('exchange_photos', 'public');
        }

        $exchange->update([
            'note' => $request->note,
            'after_photo_path' => $afterPhotoPath,
        ]);

        return response()->json([
            'message' => 'Exchange updated successfully',
            'exchange' => $exchange->load(['fromDriver', 'toDriver', 'vehicle']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleExchange $exchange): JsonResponse
    {
        // Only allow deletion if exchange is pending
        if ($exchange->status !== 'pending') {
            return response()->json(['message' => 'Cannot delete this exchange'], 403);
        }

        // Delete photos if they exist
        if ($exchange->before_photo_path) {
            Storage::disk('public')->delete($exchange->before_photo_path);
        }
        if ($exchange->after_photo_path) {
            Storage::disk('public')->delete($exchange->after_photo_path);
        }

        $exchange->delete();

        return response()->json([
            'message' => 'Exchange deleted successfully',
        ]);
    }

    /**
     * Approve an exchange request.
     */
    #[OA\Post(
        path: "/exchanges/{id}/approve",
        summary: "Approve exchange request",
        description: "Approve a vehicle exchange request (Admin only)",
        security: [["bearerAuth" => []]],
        tags: ["Exchanges"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "Exchange ID",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Exchange approved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Exchange approved successfully"),
                        new OA\Property(property: "exchange", ref: "#/components/schemas/VehicleExchange")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Admin only"),
            new OA\Response(response: 404, description: "Exchange not found"),
            new OA\Response(response: 422, description: "Exchange is not pending")
        ]
    )]
    public function approve(VehicleExchange $exchange): JsonResponse
    {
        if ($exchange->status !== 'pending') {
            return response()->json(['message' => 'Exchange is not pending'], 422);
        }

        $exchange->approve();

        return response()->json([
            'message' => 'Exchange approved successfully',
            'exchange' => $exchange->fresh()->load(['fromDriver', 'toDriver', 'vehicle']),
        ]);
    }

    /**
     * Reject an exchange request.
     */
    #[OA\Post(
        path: "/exchanges/{id}/reject",
        summary: "Reject exchange request",
        description: "Reject a vehicle exchange request (Admin only)",
        security: [["bearerAuth" => []]],
        tags: ["Exchanges"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "Exchange ID",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Exchange rejected successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Exchange rejected successfully"),
                        new OA\Property(property: "exchange", ref: "#/components/schemas/VehicleExchange")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Admin only"),
            new OA\Response(response: 404, description: "Exchange not found"),
            new OA\Response(response: 422, description: "Exchange is not pending")
        ]
    )]
    public function reject(VehicleExchange $exchange): JsonResponse
    {
        if ($exchange->status !== 'pending') {
            return response()->json(['message' => 'Exchange is not pending'], 422);
        }

        $exchange->reject();

        return response()->json([
            'message' => 'Exchange rejected successfully',
            'exchange' => $exchange->fresh()->load(['fromDriver', 'toDriver', 'vehicle']),
        ]);
    }

    /**
     * Get exchanges for the authenticated chauffeur.
     */
    #[OA\Get(
        path: "/my-exchanges",
        summary: "Get my exchanges",
        description: "Get all exchange requests for the authenticated chauffeur",
        security: [["bearerAuth" => []]],
        tags: ["Exchanges"],
        responses: [
            new OA\Response(
                response: 200,
                description: "User exchanges retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/VehicleExchange")
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
    public function myExchanges(Request $request): JsonResponse
    {
        $user = $request->user();

        $exchanges = VehicleExchange::with(['fromDriver', 'toDriver', 'vehicle'])
            ->where(function ($q) use ($user) {
                $q->where('from_driver_id', $user->id)
                    ->orWhere('to_driver_id', $user->id);
            })
            ->orderBy('request_date', 'desc')
            ->paginate(15);

        return response()->json($exchanges);
    }
}
