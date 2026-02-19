<?php

namespace App\Http\Controllers\Api;

use App\Models\Block;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Block Controller
 * Manages training blocks (sections of training forms)
 */
class BlockController extends BaseController
{
    /**
     * GET /api/v1/blocks
     * List all blocks for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = Block::where('client_id', $this->getClientId())
            ->with(['blockType', 'elements'])
            ->when($request->form_id, fn($q, $formId) => $q->where('form_id', $formId))
            ->when($request->tbt_id, fn($q, $tbtId) => $q->where('tbt_id', $tbtId))
            ->when($request->enabled !== null, fn($q) => $q->where('enabled', $request->enabled))
            ->orderBy($request->sort ?? 'sortorder', $request->order ?? 'asc');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/blocks
     * Create a new block
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tbt_id' => 'nullable|integer|exists:tr2_block_types,id',
            'form_id' => 'nullable|integer|exists:tr2_forms,id',
            'enabled' => 'boolean',
            'sortorder' => 'nullable|integer',
            'syllabus_text' => 'nullable|string',
            'guidance_text' => 'nullable|string',
        ]);

        $validated['client_id'] = $this->getClientId();

        $block = Block::create($validated);

        return $this->success($block, 'Block created successfully', 201);
    }

    /**
     * GET /api/v1/blocks/{block}
     * Get block with elements
     */
    public function show(Block $block): JsonResponse
    {
        $this->authorizeClientAccess($block);

        return $this->success(
            $block->load(['blockType', 'elements.grading', 'form'])
        );
    }

    /**
     * PUT /api/v1/blocks/{block}
     * Update block
     */
    public function update(Request $request, Block $block): JsonResponse
    {
        $this->authorizeClientAccess($block);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'tbt_id' => 'nullable|integer|exists:tr2_block_types,id',
            'form_id' => 'nullable|integer|exists:tr2_forms,id',
            'enabled' => 'boolean',
            'sortorder' => 'nullable|integer',
            'syllabus_text' => 'nullable|string',
            'guidance_text' => 'nullable|string',
        ]);

        $block->update($validated);

        return $this->success($block, 'Block updated successfully');
    }

    /**
     * DELETE /api/v1/blocks/{block}
     * Delete block
     */
    public function destroy(Block $block): JsonResponse
    {
        $this->authorizeClientAccess($block);

        $block->delete();

        return $this->success(null, 'Block deleted successfully');
    }

    private function authorizeClientAccess(Block $block): void
    {
        if ($block->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
