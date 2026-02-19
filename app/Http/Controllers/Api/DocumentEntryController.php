<?php

namespace App\Http\Controllers\Api;

use App\Models\DocumentEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Document Entry Controller
 * Manages document entries/attachments
 */
class DocumentEntryController extends BaseController
{
    /**
     * GET /api/v1/document-entries
     * List all document entries for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = DocumentEntry::where('client_id', $this->getClientId())
            ->with(['uploadedBy:id,name'])
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
            )
            ->when($request->entity_type, fn($q, $type) => 
                $q->where('entity_type', $type)
            )
            ->when($request->entity_id, fn($q, $id) => 
                $q->where('entity_id', $id)
            )
            ->when($request->document_type, fn($q, $type) => 
                $q->where('document_type', $type)
            )
            ->orderBy($request->sort ?? 'created_at', $request->order ?? 'desc');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/document-entries
     * Create a new document entry
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'entity_type' => 'nullable|string|max:100',
            'entity_id' => 'nullable|integer',
            'document_type' => 'nullable|string|max:100',
            'file_path' => 'required|string|max:500',
            'file_name' => 'required|string|max:255',
            'file_size' => 'nullable|integer',
            'mime_type' => 'nullable|string|max:100',
            'expires_at' => 'nullable|date',
        ]);

        $validated['client_id'] = $this->getClientId();
        $validated['uploaded_by'] = auth()->id();

        $document = DocumentEntry::create($validated);

        return $this->success($document, 'Document entry created successfully', 201);
    }

    /**
     * GET /api/v1/document-entries/{documentEntry}
     * Get document entry details
     */
    public function show(DocumentEntry $documentEntry): JsonResponse
    {
        $this->authorizeClientAccess($documentEntry);

        return $this->success($documentEntry->load('uploadedBy'));
    }

    /**
     * PUT /api/v1/document-entries/{documentEntry}
     * Update document entry
     */
    public function update(Request $request, DocumentEntry $documentEntry): JsonResponse
    {
        $this->authorizeClientAccess($documentEntry);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:1000',
            'document_type' => 'nullable|string|max:100',
            'expires_at' => 'nullable|date',
        ]);

        $documentEntry->update($validated);

        return $this->success($documentEntry, 'Document entry updated successfully');
    }

    /**
     * DELETE /api/v1/document-entries/{documentEntry}
     * Delete document entry
     */
    public function destroy(DocumentEntry $documentEntry): JsonResponse
    {
        $this->authorizeClientAccess($documentEntry);

        // TODO: Delete associated file from storage
        $documentEntry->delete();

        return $this->success(null, 'Document entry deleted successfully');
    }

    private function authorizeClientAccess(DocumentEntry $documentEntry): void
    {
        if ($documentEntry->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
