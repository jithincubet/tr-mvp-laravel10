<?php

namespace App\Http\Controllers\Api;

use App\Models\NotificationRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Notification Rule Controller
 * Manages automated notification rules for currency expiry alerts
 */
class NotificationRuleController extends BaseController
{
    /**
     * GET /api/v1/notification-rules
     * List all notification rules
     */
    public function index(Request $request): JsonResponse
    {
        $query = NotificationRule::where('client_id', $this->getClientId())
            ->with(['template'])
            ->orderBy('name');

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/notification-rules
     * Create a new notification rule
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'enabled' => 'boolean',
            'trigger_type' => 'required|string|in:expiring,expired,qualified',
            'trigger_days' => 'required_if:trigger_type,expiring|integer|min:1|max:365',
            'frequency' => 'required|string|in:immediate,daily,weekly',
            'recipient_type' => 'required|string|in:user,manager,admin,custom',
            'recipient_ids' => 'array',
            'recipient_ids.*' => 'integer|exists:tr2_users,id',
            'team_ids' => 'array',
            'team_ids.*' => 'integer|exists:tr2_users_collections,id',
            'endorsement_ids' => 'array',
            'endorsement_ids.*' => 'integer|exists:tr2_endorsements,id',
            'template_id' => 'nullable|integer|exists:tr2_email_templates,id',
        ]);

        $validated['client_id'] = $this->getClientId();

        $rule = NotificationRule::create($validated);

        return $this->success($rule, 'Notification rule created', 201);
    }

    /**
     * GET /api/v1/notification-rules/{rule}
     * Get rule details
     */
    public function show(NotificationRule $notificationRule): JsonResponse
    {
        $this->authorizeClientAccess($notificationRule);

        return $this->success($notificationRule->load(['template']));
    }

    /**
     * PUT /api/v1/notification-rules/{rule}
     * Update rule
     */
    public function update(Request $request, NotificationRule $notificationRule): JsonResponse
    {
        $this->authorizeClientAccess($notificationRule);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'enabled' => 'boolean',
            'trigger_type' => 'string|in:expiring,expired,qualified',
            'trigger_days' => 'integer|min:1|max:365',
            'frequency' => 'string|in:immediate,daily,weekly',
            'recipient_type' => 'string|in:user,manager,admin,custom',
            'recipient_ids' => 'array',
            'team_ids' => 'array',
            'endorsement_ids' => 'array',
            'template_id' => 'nullable|integer|exists:tr2_email_templates,id',
        ]);

        $notificationRule->update($validated);

        return $this->success($notificationRule, 'Notification rule updated');
    }

    /**
     * DELETE /api/v1/notification-rules/{rule}
     * Delete rule
     */
    public function destroy(NotificationRule $notificationRule): JsonResponse
    {
        $this->authorizeClientAccess($notificationRule);

        $notificationRule->delete();

        return $this->success(null, 'Notification rule deleted');
    }

    private function authorizeClientAccess(NotificationRule $rule): void
    {
        if ($rule->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
