<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDivisionRequest;
use App\Http\Resources\DivisionResource;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;

class DivisionController extends Controller
{
    /**
     * List all divisions for the authenticated client.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Division::query()
            ->withCount('users');

        // Search filter
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        // Include users if requested
        if ($request->boolean('include_users')) {
            $query->with('users:id,name,email,division_id');
        }

        $divisions = $query->orderBy('name')->paginate($request->input('per_page', 25));

        return DivisionResource::collection($divisions);
    }

    /**
     * Create a new division.
     */
    public function store(StoreDivisionRequest $request): JsonResponse
    {
        $division = Division::create([
            'client_id' => auth()->user()->client_id,
            ...$request->validated(),
        ]);

        return response()->json([
            'message' => 'Division created successfully.',
            'data' => new DivisionResource($division),
        ], 201);
    }

    /**
     * Get a specific division.
     */
    public function show(Division $division): DivisionResource
    {
        $division->loadCount('users');

        return new DivisionResource($division);
    }

    /**
     * Update a division.
     */
    public function update(StoreDivisionRequest $request, Division $division): JsonResponse
    {
        if ($division->is_locked) {
            return response()->json([
                'message' => 'This division is locked and cannot be modified.',
            ], 403);
        }

        $division->update($request->validated());

        return response()->json([
            'message' => 'Division updated successfully.',
            'data' => new DivisionResource($division),
        ]);
    }

    /**
     * Delete a division.
     */
    public function destroy(Division $division): JsonResponse
    {
        if ($division->is_default) {
            return response()->json([
                'message' => 'The default division cannot be deleted.',
            ], 403);
        }

        if ($division->is_locked) {
            return response()->json([
                'message' => 'This division is locked and cannot be deleted.',
            ], 403);
        }

        // Check for users in this division
        $userCount = $division->users()->count();
        if ($userCount > 0) {
            return response()->json([
                'message' => "Cannot delete division with {$userCount} assigned users. Reassign users first.",
            ], 422);
        }

        $division->delete();

        return response()->json([
            'message' => 'Division deleted successfully.',
        ]);
    }
}
