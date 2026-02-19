<?php

namespace App\Http\Controllers\Api;

use App\Models\Block;
use App\Models\BlockElement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Block Element Controller
 * Manages block element items (grading criteria within blocks)
 */
class BlockElementController extends BaseController
{
    /**
     * GET /api/v1/block-elements
     * List all block elements for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = BlockElement::whereHas('block', fn($q) => 
                $q->where('client_id', $this->getClientId())
            )
            ->with(['block:id,name,form_id', 'grading:id,name,code'])
            ->when($request->block_id, fn($q, $blockId) => 
                $q->where('parent_id', $blockId)
            )
            ->when($request->enabled !== null, fn($q) => 
                $q->where('enabled', $request->boolean('enabled'))
            )
            ->orderBy('parent_id')
            ->orderBy('sortorder');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/block-elements
     * Create a new block element
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => 'required|integer|exists:blocks,id',
            'description' => 'required|string|max:1000',
            'grading_id' => 'nullable|integer|exists:gradings,id',
            'mandatory' => 'boolean',
            'enabled' => 'boolean',
            'sortorder' => 'integer',
            'syllabus_text' => 'nullable|string',
            'guidance_text' => 'nullable|string',
            'is_critical' => 'boolean',
            'fail_triggers_additional_training' => 'boolean',
        ]);

        // Verify block belongs to client
        $block = Block::findOrFail($validated['parent_id']);
        if ($block->client_id !== $this->getClientId()) {
            return $this->forbidden('Cannot add element to block from another client');
        }

        $element = BlockElement::create($validated);

        return $this->success($element->load('grading'), 'Block element created successfully', 201);
    }

    /**
     * GET /api/v1/block-elements/{blockElement}
     * Get block element details
     */
    public function show(BlockElement $blockElement): JsonResponse
    {
        $this->authorizeClientAccess($blockElement);

        return $this->success(
            $blockElement->load(['block', 'grading'])
        );
    }

    /**
     * PUT /api/v1/block-elements/{blockElement}
     * Update block element
     */
    public function update(Request $request, BlockElement $blockElement): JsonResponse
    {
        $this->authorizeClientAccess($blockElement);

        $validated = $request->validate([
            'description' => 'string|max:1000',
            'grading_id' => 'nullable|integer|exists:gradings,id',
            'mandatory' => 'boolean',
            'enabled' => 'boolean',
            'sortorder' => 'integer',
            'syllabus_text' => 'nullable|string',
            'guidance_text' => 'nullable|string',
            'is_critical' => 'boolean',
            'fail_triggers_additional_training' => 'boolean',
        ]);

        $blockElement->update($validated);

        return $this->success($blockElement, 'Block element updated successfully');
    }

    /**
     * DELETE /api/v1/block-elements/{blockElement}
     * Delete block element
     */
    public function destroy(BlockElement $blockElement): JsonResponse
    {
        $this->authorizeClientAccess($blockElement);

        // TODO: Check for existing grades using this element
        $blockElement->delete();

        return $this->success(null, 'Block element deleted successfully');
    }

    private function authorizeClientAccess(BlockElement $blockElement): void
    {
        if ($blockElement->block->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
