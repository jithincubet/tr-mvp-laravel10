<?php

namespace App\Http\Controllers\Api;

use App\Models\BlockType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Block Type Controller
 * Manages block type definitions
 */
class BlockTypeController extends BaseController
{
    /**
     * GET /api/v1/block-types
     * List all block types for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = BlockType::where('client_id', $this->getClientId())
            ->withCount('blocks')
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
            )
            ->orderBy($request->sort ?? 'name', $request->order ?? 'asc');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/block-types
     * Create a new block type
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $validated['client_id'] = $this->getClientId();

        $blockType = BlockType::create($validated);

        return $this->success($blockType, 'Block type created successfully', 201);
    }

    /**
     * GET /api/v1/block-types/{blockType}
     * Get block type details
     */
    public function show(BlockType $blockType): JsonResponse
    {
        $this->authorizeClientAccess($blockType);

        return $this->success(
            $blockType->load('blocks')
        );
    }

    /**
     * PUT /api/v1/block-types/{blockType}
     * Update block type
     */
    public function update(Request $request, BlockType $blockType): JsonResponse
    {
        $this->authorizeClientAccess($blockType);

        $validated = $request->validate([
            'name' => 'string|max:255',
        ]);

        $blockType->update($validated);

        return $this->success($blockType, 'Block type updated successfully');
    }

    /**
     * DELETE /api/v1/block-types/{blockType}
     * Delete block type
     */
    public function destroy(BlockType $blockType): JsonResponse
    {
        $this->authorizeClientAccess($blockType);

        // TODO: Check if blocks are using this type
        $blockType->delete();

        return $this->success(null, 'Block type deleted successfully');
    }

    private function authorizeClientAccess(BlockType $blockType): void
    {
        if ($blockType->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
