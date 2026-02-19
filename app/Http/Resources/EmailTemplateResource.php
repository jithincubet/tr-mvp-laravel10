<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Email Template Resource
 * Transforms EmailTemplate model for API responses
 */
class EmailTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'notification_type' => $this->notification_type,
            'subject' => $this->subject,
            'body_html' => $this->body_html,
            'table_config' => $this->table_config,
            'is_default' => $this->is_default,
            'enabled' => $this->enabled,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
