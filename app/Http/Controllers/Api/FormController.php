<?php

namespace App\Http\Controllers\Api;

use App\Models\Form;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Form Controller
 * Manages training form templates
 */
class FormController extends BaseController
{
    /**
     * GET /api/v1/forms
     * List all forms for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = Form::where('client_id', $this->getClientId())
            ->with(['blocks.elements', 'endorsements'])
            ->when($request->enabled !== null, fn($q) => $q->where('enabled', $request->enabled))
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
     * POST /api/v1/forms
     * Create a new training form
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'enabled' => 'boolean',
            'grading_id' => 'nullable|integer|exists:tr2_gradings,id',
            'block_ids' => 'array',
            'block_ids.*' => 'integer|exists:tr2_blocks,id',
        ]);

        $validated['client_id'] = $this->getClientId();

        $form = Form::create($validated);

        // Attach blocks if provided
        if (!empty($validated['block_ids'])) {
            foreach ($validated['block_ids'] as $order => $blockId) {
                $form->blocks()->attach($blockId, ['sortorder' => $order]);
            }
        }

        return $this->success(
            $form->load(['blocks.elements']),
            'Form created successfully',
            201
        );
    }

    /**
     * GET /api/v1/forms/{form}
     * Get form with all blocks and elements
     */
    public function show(Form $form): JsonResponse
    {
        $this->authorizeClientAccess($form);

        return $this->success(
            $form->load([
                'blocks.elements.grading',
                'blocks.blockType',
                'endorsements',
                'instructorEndorsements',
                'exposureRequirements.exposureType',
            ])
        );
    }

    /**
     * PUT /api/v1/forms/{form}
     * Update form details
     */
    public function update(Request $request, Form $form): JsonResponse
    {
        $this->authorizeClientAccess($form);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'enabled' => 'boolean',
            'grading_id' => 'nullable|integer|exists:tr2_gradings,id',
            'block_ids' => 'array',
            'block_ids.*' => 'integer|exists:tr2_blocks,id',
        ]);

        $form->update($validated);

        // Sync blocks if provided
        if (isset($validated['block_ids'])) {
            $syncData = [];
            foreach ($validated['block_ids'] as $order => $blockId) {
                $syncData[$blockId] = ['sortorder' => $order];
            }
            $form->blocks()->sync($syncData);
        }

        return $this->success($form->fresh(['blocks']), 'Form updated successfully');
    }

    /**
     * DELETE /api/v1/forms/{form}
     * Delete a form
     */
    public function destroy(Form $form): JsonResponse
    {
        $this->authorizeClientAccess($form);

        // Check for associated events
        $eventCount = $form->events()->count();
        if ($eventCount > 0) {
            return $this->error(
                "Cannot delete form with {$eventCount} associated events",
                'HAS_DEPENDENCIES',
                409
            );
        }

        $form->delete();

        return $this->success(null, 'Form deleted successfully');
    }

    /**
     * PUT /api/v1/forms/{form}/endorsements
     * Set form endorsement associations
     */
    public function setEndorsements(Request $request, Form $form): JsonResponse
    {
        $this->authorizeClientAccess($form);

        $validated = $request->validate([
            'endorsement_ids' => 'required|array',
            'endorsement_ids.*' => 'integer|exists:tr2_endorsements,id',
            'instructor_endorsement_ids' => 'array',
            'instructor_endorsement_ids.*' => 'integer|exists:tr2_endorsements,id',
        ]);

        $form->endorsements()->sync($validated['endorsement_ids']);

        if (isset($validated['instructor_endorsement_ids'])) {
            $form->instructorEndorsements()->sync($validated['instructor_endorsement_ids']);
        }

        return $this->success(
            $form->load(['endorsements', 'instructorEndorsements']),
            'Form endorsements updated'
        );
    }

    private function authorizeClientAccess(Form $form): void
    {
        if ($form->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
