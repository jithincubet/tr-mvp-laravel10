<?php

namespace App\Http\Controllers\Api;

use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Audit Log Controller
 * Manages audit trail records
 */
class AuditLogController extends BaseController
{
    /**
     * GET /api/v1/audit-log
     * List audit log entries for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::where('client_id', $this->getClientId())
            ->with(['performedBy:id,name,email'])
            ->when($request->entity_type, fn($q, $type) => 
                $q->where('entity_type', $type)
            )
            ->when($request->entity_id, fn($q, $id) => 
                $q->where('entity_id', $id)
            )
            ->when($request->action, fn($q, $action) => 
                $q->where('action', $action)
            )
            ->when($request->performed_by, fn($q, $userId) => 
                $q->where('performed_by', $userId)
            )
            ->when($request->date_from, fn($q, $date) => 
                $q->whereDate('created_at', '>=', $date)
            )
            ->when($request->date_to, fn($q, $date) => 
                $q->whereDate('created_at', '<=', $date)
            )
            ->orderBy('created_at', 'desc');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->limit(100)->get());
    }

    /**
     * POST /api/v1/audit-log
     * Create an audit log entry
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entity_type' => 'required|string|max:100',
            'entity_id' => 'required|integer',
            'action' => 'required|string|max:100',
            'details' => 'nullable|array',
        ]);

        $validated['client_id'] = $this->getClientId();
        $validated['performed_by'] = auth()->id();

        $auditLog = AuditLog::create($validated);

        return $this->success($auditLog, 'Audit log entry created', 201);
    }
}
