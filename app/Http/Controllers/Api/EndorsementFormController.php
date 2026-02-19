<?php

namespace App\Http\Controllers\Api;

use App\Models\EndorsementForm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endorsement Form Controller
 * Manages endorsement form definitions with custom fields
 */
class EndorsementFormController extends BaseController
{
    /**
     * GET /api/v1/endorsement-forms
     * List all endorsement forms for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = EndorsementForm::where('client_id', $this->getClientId())
            ->withCount('fields')
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
            )
            ->when($request->disabled !== null, fn($q) => 
                $q->where('disabled', $request->boolean('disabled'))
            )
            ->orderBy($request->sort ?? 'sort_order', $request->order ?? 'asc');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/endorsement-forms
     * Create a new endorsement form
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'reference' => 'nullable|string|max:100',
            'sort_order' => 'integer',
            'disabled' => 'boolean',
            'properties' => 'nullable|array',
        ]);

        $validated['client_id'] = $this->getClientId();

        $form = EndorsementForm::create($validated);

        return $this->success($form, 'Endorsement form created successfully', 201);
    }

    /**
     * GET /api/v1/endorsement-forms/{endorsementForm}
     * Get endorsement form details with fields
     */
    public function show(EndorsementForm $endorsementForm): JsonResponse
    {
        $this->authorizeClientAccess($endorsementForm);

        return $this->success(
            $endorsementForm->load(['fields' => fn($q) => $q->orderBy('sort_order')])
        );
    }

    /**
     * PUT /api/v1/endorsement-forms/{endorsementForm}
     * Update endorsement form
     */
    public function update(Request $request, EndorsementForm $endorsementForm): JsonResponse
    {
        $this->authorizeClientAccess($endorsementForm);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:1000',
            'reference' => 'nullable|string|max:100',
            'sort_order' => 'integer',
            'disabled' => 'boolean',
            'properties' => 'nullable|array',
        ]);

        $endorsementForm->update($validated);

        return $this->success($endorsementForm, 'Endorsement form updated successfully');
    }

    /**
     * DELETE /api/v1/endorsement-forms/{endorsementForm}
     * Delete endorsement form
     */
    public function destroy(EndorsementForm $endorsementForm): JsonResponse
    {
        $this->authorizeClientAccess($endorsementForm);

        // TODO: Check if endorsements are using this form
        $endorsementForm->fields()->delete();
        $endorsementForm->delete();

        return $this->success(null, 'Endorsement form deleted successfully');
    }

    private function authorizeClientAccess(EndorsementForm $endorsementForm): void
    {
        if ($endorsementForm->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
