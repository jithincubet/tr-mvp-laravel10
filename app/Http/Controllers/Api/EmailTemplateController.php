<?php

namespace App\Http\Controllers\Api;

use App\Models\EmailTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Email Template Controller
 * Manages email templates for notifications
 */
class EmailTemplateController extends BaseController
{
    /**
     * GET /api/v1/email-templates
     * List all email templates for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = EmailTemplate::where(function ($q) {
                $q->where('client_id', $this->getClientId())
                  ->orWhere('is_default', true);
            })
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
            )
            ->when($request->notification_type, fn($q, $type) => 
                $q->where('notification_type', $type)
            )
            ->when($request->enabled !== null, fn($q) => 
                $q->where('enabled', $request->boolean('enabled'))
            )
            ->orderBy($request->sort ?? 'name', $request->order ?? 'asc');

        if ($request->has('page')) {
            return $this->paginated($query->paginate($request->per_page ?? 50));
        }

        return $this->success($query->get());
    }

    /**
     * POST /api/v1/email-templates
     * Create a new email template
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'notification_type' => 'required|string|max:100',
            'subject' => 'required|string|max:255',
            'body_html' => 'required|string',
            'table_config' => 'nullable|array',
            'enabled' => 'boolean',
        ]);

        $validated['client_id'] = $this->getClientId();
        $validated['is_default'] = false;

        $template = EmailTemplate::create($validated);

        return $this->success($template, 'Email template created successfully', 201);
    }

    /**
     * GET /api/v1/email-templates/{emailTemplate}
     * Get email template details
     */
    public function show(EmailTemplate $emailTemplate): JsonResponse
    {
        $this->authorizeClientAccess($emailTemplate);

        return $this->success($emailTemplate);
    }

    /**
     * PUT /api/v1/email-templates/{emailTemplate}
     * Update email template
     */
    public function update(Request $request, EmailTemplate $emailTemplate): JsonResponse
    {
        $this->authorizeClientAccess($emailTemplate);

        // Cannot edit default templates directly; must clone
        if ($emailTemplate->is_default) {
            return $this->error('Cannot modify default templates. Clone to customize.', 'FORBIDDEN', 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'notification_type' => 'string|max:100',
            'subject' => 'string|max:255',
            'body_html' => 'string',
            'table_config' => 'nullable|array',
            'enabled' => 'boolean',
        ]);

        $emailTemplate->update($validated);

        return $this->success($emailTemplate, 'Email template updated successfully');
    }

    /**
     * DELETE /api/v1/email-templates/{emailTemplate}
     * Delete email template
     */
    public function destroy(EmailTemplate $emailTemplate): JsonResponse
    {
        $this->authorizeClientAccess($emailTemplate);

        if ($emailTemplate->is_default) {
            return $this->error('Cannot delete default templates', 'FORBIDDEN', 403);
        }

        $emailTemplate->delete();

        return $this->success(null, 'Email template deleted successfully');
    }

    private function authorizeClientAccess(EmailTemplate $emailTemplate): void
    {
        // Allow access to default templates for viewing
        if ($emailTemplate->is_default) {
            return;
        }

        if ($emailTemplate->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
