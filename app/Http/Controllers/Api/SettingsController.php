<?php

namespace App\Http\Controllers\Api;

use App\Models\EmailTracking;
use App\Models\InvoiceEmailTemplate;
use App\Models\InvoiceSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Settings Controller
 * Manages invoice settings, email templates, email tracking, and cron jobs
 */
class SettingsController extends BaseController
{
    /**
     * GET /api/v1/billing/settings
     */
    public function getSettings(): JsonResponse
    {
        return $this->success(InvoiceSetting::instance());
    }

    /**
     * PUT /api/v1/billing/settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'sometimes|string|max:255',
            'company_reference' => 'sometimes|string|max:255',
            'next_invoice_number' => 'sometimes|integer|min:1',
            'sales_manager_email' => 'sometimes|email|max:255',
            'renewal_reminder_days' => 'sometimes|integer|min:0',
            'date_format' => 'sometimes|string|max:20',
            'vat_percentage' => 'sometimes|numeric|min:0',
            'company_address' => 'nullable|string|max:500',
            'company_city' => 'nullable|string|max:100',
            'company_postal_code' => 'nullable|string|max:20',
            'company_country' => 'nullable|string|max:100',
            'bank_giro' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:50',
            'payment_terms_days' => 'nullable|integer|min:0',
            'interest_rate' => 'nullable|string|max:20',
            'legal_disclaimer' => 'nullable|string|max:2000',
            'bic' => 'nullable|string|max:20',
            'vat_reg_no' => 'nullable|string|max:50',
            'org_no' => 'nullable|string|max:50',
            'bank' => 'nullable|string|max:100',
            'local_of_board' => 'nullable|string|max:100',
            'logo_url' => 'nullable|url|max:500',
        ]);

        $settings = InvoiceSetting::instance();
        $settings->update($validated);

        return $this->success($settings, 'Settings updated successfully');
    }

    /**
     * GET /api/v1/billing/settings/email-templates/{type}
     */
    public function getEmailTemplate(string $type): JsonResponse
    {
        $template = InvoiceEmailTemplate::where('template_type', $type)->first();

        if (!$template) {
            return $this->notFound('Email template');
        }

        return $this->success($template);
    }

    /**
     * PUT /api/v1/billing/settings/email-templates/{type}
     */
    public function updateEmailTemplate(Request $request, string $type): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:500',
            'body' => 'required|string|max:10000',
        ]);

        $template = InvoiceEmailTemplate::updateOrCreate(
            ['template_type' => $type],
            $validated
        );

        return $this->success($template, 'Email template updated successfully');
    }

    /**
     * GET /api/v1/billing/settings/email-tracking
     * Optionally filter by invoice_id
     */
    public function listTracking(Request $request): JsonResponse
    {
        $query = EmailTracking::query();

        if ($request->has('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }

        return $this->success($query->get());
    }

    /**
     * GET /api/v1/billing/settings/email-tracking/stats
     */
    public function trackingStats(): JsonResponse
    {
        $total = EmailTracking::count();

        if ($total === 0) {
            return $this->success([
                'total' => 0,
                'delivered' => 0,
                'opened' => 0,
                'clicked' => 0,
                'bounced' => 0,
                'deliveryRate' => 0,
                'openRate' => 0,
                'clickRate' => 0,
                'bounceRate' => 0,
            ]);
        }

        $delivered = EmailTracking::whereNotNull('delivered_at')->count();
        $opened = EmailTracking::whereNotNull('opened_at')->count();
        $clicked = EmailTracking::whereNotNull('clicked_at')->count();
        $bounced = EmailTracking::whereNotNull('bounced_at')->count();

        return $this->success([
            'total' => $total,
            'delivered' => $delivered,
            'opened' => $opened,
            'clicked' => $clicked,
            'bounced' => $bounced,
            'deliveryRate' => round(($delivered / $total) * 100, 2),
            'openRate' => $delivered > 0 ? round(($opened / $delivered) * 100, 2) : 0,
            'clickRate' => $delivered > 0 ? round(($clicked / $delivered) * 100, 2) : 0,
            'bounceRate' => round(($bounced / $total) * 100, 2),
        ]);
    }

    /**
     * GET /api/v1/billing/settings/cron-jobs
     */
    public function listCronJobs(): JsonResponse
    {
        // Stub: In production, this would query pg_cron or a cron jobs table
        $jobs = DB::select("SELECT jobid, jobname, schedule, active FROM cron.job ORDER BY jobid");

        return $this->success($jobs);
    }

    /**
     * PATCH /api/v1/billing/settings/cron-jobs/{name}
     * Toggle a cron job
     */
    public function toggleCron(Request $request, string $name): JsonResponse
    {
        $validated = $request->validate([
            'active' => 'required|boolean',
        ]);

        DB::statement("UPDATE cron.job SET active = ? WHERE jobname = ?", [
            $validated['active'],
            $name,
        ]);

        $status = $validated['active'] ? 'enabled' : 'disabled';

        return $this->success(null, "Cron job '{$name}' {$status}");
    }

    /**
     * PUT /api/v1/billing/settings/cron-jobs/{name}
     * Update cron schedule
     */
    public function updateCronSchedule(Request $request, string $name): JsonResponse
    {
        $validated = $request->validate([
            'schedule' => 'required|string|max:100',
        ]);

        DB::statement("UPDATE cron.job SET schedule = ? WHERE jobname = ?", [
            $validated['schedule'],
            $name,
        ]);

        return $this->success(null, 'Cron job schedule updated');
    }
}