<?php

namespace App\Http\Controllers\Api;

use App\Models\ClientUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Client User Controller
 * Manages billing contacts / client users
 */
class ClientUserController extends BaseController
{
    /**
     * GET /api/v1/billing/client-users
     */
    public function index(): JsonResponse
    {
        $users = ClientUser::all();

        return $this->success($users);
    }

    /**
     * POST /api/v1/billing/client-users
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|integer|exists:tr2_clients,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $user = ClientUser::create($validated);

        return $this->success($user, 'Client user created successfully', 201);
    }

    /**
     * PUT /api/v1/billing/client-users/{id}
     */
    public function update(Request $request, ClientUser $clientUser): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $clientUser->update($validated);

        return $this->success($clientUser, 'Client user updated successfully');
    }

    /**
     * DELETE /api/v1/billing/client-users/{id}
     */
    public function destroy(ClientUser $clientUser): JsonResponse
    {
        $clientUser->delete();

        return $this->success(null, 'Client user deleted successfully');
    }
}