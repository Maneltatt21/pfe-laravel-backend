<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: "/users",
        summary: "Get all users",
        description: "Retrieve a list of all users (Admin only)",
        security: [["bearerAuth" => []]],
        tags: ["Users"],
        parameters: [
            new OA\Parameter(
                name: "role",
                in: "query",
                description: "Filter by user role",
                required: false,
                schema: new OA\Schema(type: "string", enum: ["admin", "chauffeur"])
            ),
            new OA\Parameter(
                name: "search",
                in: "query",
                description: "Search by name or email",
                required: false,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Users retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/User")
                        ),
                        new OA\Property(property: "current_page", type: "integer"),
                        new OA\Property(property: "last_page", type: "integer"),
                        new OA\Property(property: "per_page", type: "integer"),
                        new OA\Property(property: "total", type: "integer")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Admin only")
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = User::with('vehicle');

        // Filter by role if provided
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15);

        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: "/users",
        summary: "Create new user",
        description: "Create a new user account (Admin only)",
        security: [["bearerAuth" => []]],
        tags: ["Users"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password", "password_confirmation", "role"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Jane Doe"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "jane@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password123"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "password123"),
                    new OA\Property(property: "role", type: "string", enum: ["admin", "chauffeur"], example: "chauffeur"),
                    new OA\Property(property: "vehicle_id", type: "integer", nullable: true, example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "User created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "User created successfully"),
                        new OA\Property(property: "user", ref: "#/components/schemas/User")
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,chauffeur',
            'vehicle_id' => 'nullable|exists:vehicles,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'vehicle_id' => $request->vehicle_id,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load('vehicle'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'user' => $user->load(['vehicle', 'initiatedExchanges', 'receivedExchanges']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,chauffeur',
            'vehicle_id' => 'nullable|exists:vehicles,id',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'vehicle_id' => $request->vehicle_id,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load('vehicle'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        // Prevent deleting the last admin
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return response()->json([
                'message' => 'Cannot delete the last admin user',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Assign a vehicle to a user.
     */
    #[OA\Post(
        path: "/users/{id}/assign-vehicle",
        summary: "Assign vehicle to user",
        description: "Assign a vehicle to a specific user (Admin only)",
        security: [["bearerAuth" => []]],
        tags: ["Users"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "User ID",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["vehicle_id"],
                properties: [
                    new OA\Property(property: "vehicle_id", type: "integer", example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Vehicle assigned successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Vehicle assigned successfully"),
                        new OA\Property(property: "user", ref: "#/components/schemas/User")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden - Admin only"),
            new OA\Response(response: 404, description: "User not found"),
            new OA\Response(
                response: 422,
                description: "Vehicle already assigned",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Vehicle is already assigned to another user"),
                        new OA\Property(property: "assigned_to", type: "string", example: "John Doe")
                    ]
                )
            )
        ]
    )]
    public function assignVehicle(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        // Check if vehicle is already assigned to another user
        $existingAssignment = User::where('vehicle_id', $request->vehicle_id)
            ->where('id', '!=', $user->id)
            ->first();

        if ($existingAssignment) {
            return response()->json([
                'message' => 'Vehicle is already assigned to another user',
                'assigned_to' => $existingAssignment->name,
            ], 422);
        }

        $user->update(['vehicle_id' => $request->vehicle_id]);

        return response()->json([
            'message' => 'Vehicle assigned successfully',
            'user' => $user->load('vehicle'),
        ]);
    }
}
