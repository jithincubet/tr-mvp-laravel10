<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Sector Resource
 * Transforms SessionSector model for API responses
 */
class SectorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sector_number' => $this->sector_number,
            'departure_airport' => $this->departure_airport,
            'arrival_airport' => $this->arrival_airport,
            'flight_number' => $this->flight_number,
            'aircraft_type' => $this->aircraft_type,
            'aircraft_registration' => $this->aircraft_registration,
            'sector_date' => $this->sector_date?->format('Y-m-d'),
            'duty_type' => $this->duty_type,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
