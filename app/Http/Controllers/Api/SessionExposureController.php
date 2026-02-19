<?php

namespace App\Http\Controllers\Api;

use App\Models\EventSession;
use App\Models\SessionExposure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Session Exposure Controller
 * Manages exposure records for training sessions
 */
class SessionExposureController extends BaseController
{
    /**
     * GET /api/v1/sessions/{session}/exposures
     * List all exposures for a session
     */
    public function index(Request $request, EventSession $session): JsonResponse
    {
        $this->authorizeSessionAccess($session);

        $exposures = SessionExposure::where('session_id', $session->id)
            ->with('exposureType:id,name,code')
            ->orderBy('created_at')
            ->get();

        return $this->success($exposures);
    }

    /**
     * POST /api/v1/sessions/{session}/exposures
     * Create or update exposures for a session (bulk operation)
     */
    public function store(Request $request, EventSession $session): JsonResponse
    {
        $this->authorizeSessionAccess($session);

        $validated = $request->validate([
            'exposures' => 'required|array',
            'exposures.*.exposure_type_id' => 'required|integer|exists:exposure_types,id',
            'exposures.*.count' => 'integer|min:0',
            'exposures.*.notes' => 'nullable|string|max:500',
        ]);

        // Delete existing exposures and recreate
        SessionExposure::where('session_id', $session->id)->delete();

        $results = [];
        foreach ($validated['exposures'] as $exposureData) {
            if (($exposureData['count'] ?? 0) > 0) {
                $exposure = SessionExposure::create([
                    'session_id' => $session->id,
                    'exposure_type_id' => $exposureData['exposure_type_id'],
                    'count' => $exposureData['count'],
                    'notes' => $exposureData['notes'] ?? null,
                ]);
                $results[] = $exposure;
            }
        }

        return $this->success($results, 'Exposures saved successfully');
    }

    private function authorizeSessionAccess(EventSession $session): void
    {
        if ($session->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
