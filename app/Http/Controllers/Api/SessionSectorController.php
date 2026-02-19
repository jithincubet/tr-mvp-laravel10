<?php

namespace App\Http\Controllers\Api;

use App\Models\EventSession;
use App\Models\SessionSector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Session Sector Controller
 * Manages sector/route logs for training sessions
 */
class SessionSectorController extends BaseController
{
    /**
     * GET /api/v1/sessions/{session}/sectors
     * List all sectors for a session
     */
    public function index(Request $request, EventSession $session): JsonResponse
    {
        $this->authorizeSessionAccess($session);

        $sectors = SessionSector::where('session_id', $session->id)
            ->orderBy('sequence')
            ->get();

        return $this->success($sectors);
    }

    /**
     * POST /api/v1/sessions/{session}/sectors
     * Create or update sectors for a session (bulk operation)
     */
    public function store(Request $request, EventSession $session): JsonResponse
    {
        $this->authorizeSessionAccess($session);

        $validated = $request->validate([
            'sectors' => 'required|array',
            'sectors.*.id' => 'nullable|integer',
            'sectors.*.departure' => 'required|string|max:10',
            'sectors.*.arrival' => 'required|string|max:10',
            'sectors.*.flight_number' => 'nullable|string|max:20',
            'sectors.*.aircraft_registration' => 'nullable|string|max:20',
            'sectors.*.sector_type' => 'nullable|string|max:50',
            'sectors.*.is_night' => 'boolean',
            'sectors.*.sequence' => 'integer',
        ]);

        // Delete existing sectors and recreate
        SessionSector::where('session_id', $session->id)->delete();

        $results = [];
        foreach ($validated['sectors'] as $index => $sectorData) {
            $sector = SessionSector::create([
                'session_id' => $session->id,
                'departure' => $sectorData['departure'],
                'arrival' => $sectorData['arrival'],
                'flight_number' => $sectorData['flight_number'] ?? null,
                'aircraft_registration' => $sectorData['aircraft_registration'] ?? null,
                'sector_type' => $sectorData['sector_type'] ?? null,
                'is_night' => $sectorData['is_night'] ?? false,
                'sequence' => $sectorData['sequence'] ?? $index + 1,
            ]);
            $results[] = $sector;
        }

        return $this->success($results, 'Sectors saved successfully');
    }

    private function authorizeSessionAccess(EventSession $session): void
    {
        if ($session->client_id !== $this->getClientId()) {
            abort(403, 'Access denied');
        }
    }
}
