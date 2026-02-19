<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduledReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'report_type' => $this->report_type,
            'frequency' => $this->frequency,
            'schedule_time' => $this->schedule_time,
            'schedule_day' => $this->schedule_day,
            'recipients' => $this->recipients,
            'filters' => $this->filters,
            'format' => $this->format,
            'enabled' => $this->enabled,
            'last_run_at' => $this->last_run_at?->toIso8601String(),
            'next_run_at' => $this->next_run_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
