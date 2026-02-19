<?php

namespace App\Http\Controllers\Api;

use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Client Controller
 * Master admin only - manages client organizations
 */
class ClientController extends BaseController
{
    /**
     * GET /api/v1/clients
     * List all clients (master admin only)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Client::query()
            ->withCount('users')
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('icao', 'like', "%{$search}%")
            )
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->orderBy($request->sort ?? 'name', $request->order ?? 'asc');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/clients
     * Create a new client organization
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_legal' => 'nullable|string|max:255',
            'icao' => 'nullable|string|max:4',
            'iata' => 'nullable|string|max:3',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'postal_code' => 'nullable|string|max:20',
            'status' => 'string|in:active,inactive,trial,suspended',
            'contract' => 'string|in:standard,enterprise,trial',
            'plan' => 'nullable|string|max:50',
        ]);

        $client = Client::create($validated);

        return $this->success($client, 'Client created successfully', 201);
    }

    /**
     * GET /api/v1/clients/{client}
     * Get client details
     */
    public function show(Client $client): JsonResponse
    {
        return $this->success(
            $client->loadCount(['users', 'events', 'endorsements'])
        );
    }

    /**
     * PUT /api/v1/clients/{client}
     * Update client
     */
    public function update(Request $request, Client $client): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'name_legal' => 'nullable|string|max:255',
            'icao' => 'nullable|string|max:4',
            'iata' => 'nullable|string|max:3',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'postal_code' => 'nullable|string|max:20',
            'status' => 'string|in:active,inactive,trial,suspended',
            'contract' => 'string|in:standard,enterprise,trial',
            'plan' => 'nullable|string|max:50',
            'date_format' => 'nullable|string|max:20',
            'currency' => 'nullable|string|max:3',
        ]);

        $client->update($validated);

        return $this->success($client, 'Client updated successfully');
    }

    /**
     * DELETE /api/v1/clients/{client}
     * Soft delete client
     */
    public function destroy(Client $client): JsonResponse
    {
        // Prevent deletion of clients with active users
        $activeUsers = $client->users()->where('status', 'active')->count();
        if ($activeUsers > 0) {
            return $this->error(
                "Cannot delete client with {$activeUsers} active users",
                'HAS_DEPENDENCIES',
                409
            );
        }

        $client->delete();

        return $this->success(null, 'Client deleted successfully');
    }
}
