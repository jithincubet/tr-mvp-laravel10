<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCertificateTemplateRequest;
use App\Http\Resources\CertificateTemplateResource;
use App\Models\CertificateTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    /**
     * List all certificate templates for the authenticated client.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = CertificateTemplate::query();

        // Filter by enabled status
        if ($request->has('enabled')) {
            $query->where('enabled', $request->boolean('enabled'));
        }

        // Filter by page size
        if ($request->has('page_size')) {
            $query->where('page_size', $request->input('page_size'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $templates = $query->orderBy('name')->paginate($request->input('per_page', 25));

        return CertificateTemplateResource::collection($templates);
    }

    /**
     * Create a new certificate template.
     */
    public function store(StoreCertificateTemplateRequest $request): JsonResponse
    {
        $template = CertificateTemplate::create([
            'client_id' => auth()->user()->client_id,
            ...$request->validated(),
        ]);

        return response()->json([
            'message' => 'Certificate template created successfully.',
            'data' => new CertificateTemplateResource($template),
        ], 201);
    }

    /**
     * Get a specific certificate template.
     */
    public function show(CertificateTemplate $certificateTemplate): CertificateTemplateResource
    {
        return new CertificateTemplateResource($certificateTemplate);
    }

    /**
     * Update a certificate template.
     */
    public function update(StoreCertificateTemplateRequest $request, CertificateTemplate $certificateTemplate): JsonResponse
    {
        $certificateTemplate->update($request->validated());

        return response()->json([
            'message' => 'Certificate template updated successfully.',
            'data' => new CertificateTemplateResource($certificateTemplate),
        ]);
    }

    /**
     * Delete a certificate template.
     */
    public function destroy(CertificateTemplate $certificateTemplate): JsonResponse
    {
        $certificateTemplate->delete();

        return response()->json([
            'message' => 'Certificate template deleted successfully.',
        ]);
    }

    /**
     * Generate a PDF certificate for a user.
     */
    public function generate(Request $request, CertificateTemplate $certificateTemplate): JsonResponse
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:tr2_users,id'],
            'training_id' => ['nullable', 'integer', 'exists:tr2_training,id'],
            'completion_date' => ['nullable', 'date'],
        ]);

        $user = \App\Models\User::findOrFail($request->input('user_id'));
        $completionDate = $request->input('completion_date', now()->toDateString());

        // Tag substitution mapping
        $tags = [
            '{{trainee_name}}' => $user->name,
            '{{trainee_email}}' => $user->email,
            '{{completion_date}}' => $completionDate,
            '{{certificate_id}}' => strtoupper(uniqid('CERT-')),
            '{{issue_date}}' => now()->toDateString(),
            '{{company_name}}' => auth()->user()->client->name ?? 'Company',
        ];

        // If training is provided, add training-specific tags
        if ($request->has('training_id')) {
            $training = \App\Models\Training::find($request->input('training_id'));
            if ($training) {
                $tags['{{training_name}}'] = $training->name;
                $tags['{{training_description}}'] = $training->description ?? '';
            }
        }

        // Process canvas data with tag substitution
        $canvasData = $certificateTemplate->canvas_data;
        if (is_array($canvasData)) {
            $canvasData = $this->substituteTagsInCanvas($canvasData, $tags);
        }

        // In production, this would generate an actual PDF
        // For now, return the processed data
        return response()->json([
            'message' => 'Certificate generated successfully.',
            'data' => [
                'template_id' => $certificateTemplate->id,
                'user_id' => $user->id,
                'tags' => $tags,
                'canvas_data' => $canvasData,
                'page_size' => $certificateTemplate->page_size,
                'page_orientation' => $certificateTemplate->page_orientation,
                // In production: 'pdf_url' => $pdfUrl,
            ],
        ]);
    }

    /**
     * Recursively substitute tags in canvas data.
     */
    private function substituteTagsInCanvas(array $canvasData, array $tags): array
    {
        array_walk_recursive($canvasData, function (&$value) use ($tags) {
            if (is_string($value)) {
                $value = str_replace(array_keys($tags), array_values($tags), $value);
            }
        });

        return $canvasData;
    }
}
