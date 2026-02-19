<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Notification Controller
 * Manages notification records
 */
class NotificationController extends BaseController
{
    /**
     * GET /api/v1/notifications
     * List all notifications for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = Notification::where('client_id', $this->getClientId())
            ->with(['user:id,name,email'])
            ->when($request->user_id, fn($q, $userId) => 
                $q->where('user_id', $userId)
            )
            ->when($request->type, fn($q, $type) => 
                $q->where('type', $type)
            )
            ->when($request->read !== null, fn($q) => 
                $q->where('read', $request->boolean('read'))
            )
            ->when($request->date_from, fn($q, $date) => 
                $q->whereDate('created_at', '>=', $date)
            )
            ->when($request->date_to, fn($q, $date) => 
                $q->whereDate('created_at', '<=', $date)
            )
            ->orderBy($request->sort ?? 'created_at', $request->order ?? 'desc');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/notifications
     * Create a new notification
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'type' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'data' => 'nullable|array',
            'action_url' => 'nullable|string|max:500',
            'priority' => 'string|in:low,normal,high,urgent',
        ]);

        $validated['client_id'] = $this->getClientId();
        $validated['read'] = false;

        $notification = Notification::create($validated);

        return $this->success($notification, 'Notification created successfully', 201);
    }

    /**
     * GET /api/v1/notifications/{notification}
     * Get notification details
     */
    public function show(Notification $notification): JsonResponse
    {
        $this->authorizeClientAccess($notification);

        return $this->success($notification->load('user'));
    }

    /**
     * PUT /api/v1/notifications/{notification}
     * Update notification (typically mark as read)
     */
    public function update(Request $request, Notification $notification): JsonResponse
    {
        $this->authorizeClientAccess($notification);

        $validated = $request->validate([
            'read' => 'boolean',
            'read_at' => 'nullable|date',
        ]);

        // Auto-set read_at when marking as read
        if (isset($validated['read']) && $validated['read'] && !$notification->read_at) {
            $validated['read_at'] = now();
        }

        $notification->update($validated);

        return $this->success($notification, 'Notification updated successfully');
    }

    /**
     * DELETE /api/v1/notifications/{notification}
     * Delete notification
     */
    public function destroy(Notification $notification): JsonResponse
    {
        $this->authorizeClientAccess($notification);

        $notification->delete();

        return $this->success(null, 'Notification deleted successfully');
    }

    private function authorizeClientAccess(Notification $notification): void
    {
        if ($notification->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
