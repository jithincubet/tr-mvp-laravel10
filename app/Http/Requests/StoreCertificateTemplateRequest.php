<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCertificateTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'page_size' => ['required', 'string', 'in:A4,Letter,Legal'],
            'page_orientation' => ['required', 'string', 'in:portrait,landscape'],
            'background_image' => ['nullable', 'string', 'max:500'],
            'canvas_data' => ['nullable', 'array'],
            'enabled' => ['sometimes', 'boolean'],
        ];
    }
}
