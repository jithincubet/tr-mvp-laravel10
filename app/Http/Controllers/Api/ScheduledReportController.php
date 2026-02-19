<?php

namespace App\Http\Controllers\Api;

use App\Models\ScheduledReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Scheduled Report Controller
 * Manages automated/scheduled reports
 */
class ScheduledReportController extends BaseController
{
    /**
     * GET /api/v1/scheduled-reports
     * List all scheduled reports for the current client
     */
    public function index(Request $request): JsonResponse
    {
        $query = ScheduledReport::where('client_id', $this->getClientId())
            ->with(['createdBy:id,name'])
            ->when($request->search, fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
            )
            ->when($request->report_type, fn($q, $type) => 
                $q->where('report_type', $type)
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
     * POST /api/v1/scheduled-reports
     * Create a new scheduled report
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'report_type' => 'required|string|max:100',
            'schedule' => 'required|string|max:100', // cron expression or preset
            'recipients' => 'required|array',
            'recipients.*' => 'email',
            'parameters' => 'nullable|array',
            'format' => 'string|in:pdf,csv,xlsx',
            'enabled' => 'boolean',
        ]);

        $validated['client_id'] = $this->getClientId();
        $validated['created_by'] = auth()->id();

        $report = ScheduledReport::create($validated);

        return $this->success($report, 'Scheduled report created successfully', 201);
    }

    /**
     * GET /api/v1/scheduled-reports/{scheduledReport}
     * Get scheduled report details
     */
    public function show(ScheduledReport $scheduledReport): JsonResponse
    {
        $this->authorizeClientAccess($scheduledReport);

        return $this->success(
            $scheduledReport->load(['createdBy', 'executions' => fn($q) => $q->latest()->limit(10)])
        );
    }

    /**
     * PUT /api/v1/scheduled-reports/{scheduledReport}
     * Update scheduled report
     */
    public function update(Request $request, ScheduledReport $scheduledReport): JsonResponse
    {
        $this->authorizeClientAccess($scheduledReport);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string|max:1000',
            'report_type' => 'string|max:100',
            'schedule' => 'string|max:100',
            'recipients' => 'array',
            'recipients.*' => 'email',
            'parameters' => 'nullable|array',
            'format' => 'string|in:pdf,csv,xlsx',
            'enabled' => 'boolean',
        ]);

        $scheduledReport->update($validated);

        return $this->success($scheduledReport, 'Scheduled report updated successfully');
    }

    /**
     * PATCH /api/v1/scheduled-reports/{report}/toggle
     * Toggle scheduled report enabled status
     */
    public function toggle(ScheduledReport $scheduledReport): JsonResponse
    {
        $this->authorizeClientAccess($scheduledReport);

        $scheduledReport->update([
            'enabled' => !$scheduledReport->enabled,
        ]);

        $status = $scheduledReport->enabled ? 'enabled' : 'disabled';

        return $this->success($scheduledReport, "Scheduled report {$status} successfully");
    }

    /**
     * DELETE /api/v1/scheduled-reports/{scheduledReport}
     * Delete scheduled report
     */
    public function destroy(ScheduledReport $scheduledReport): JsonResponse
    {
        $this->authorizeClientAccess($scheduledReport);

        $scheduledReport->delete();

        return $this->success(null, 'Scheduled report deleted successfully');
    }

    private function authorizeClientAccess(ScheduledReport $scheduledReport): void
    {
        if ($scheduledReport->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
