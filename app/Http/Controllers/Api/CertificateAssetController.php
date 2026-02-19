<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CertificateAsset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class CertificateAssetController extends Controller
{
    /**
     * List all certificate assets for the authenticated client.
     */
    public function index(Request $request): JsonResponse
    {
        $query = CertificateAsset::query();

        // Filter by file type
        if ($request->has('file_type')) {
            $query->where('file_type', $request->input('file_type'));
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        $assets = $query->orderByDesc('created_at')
            ->paginate($request->input('per_page', 25));

        // Add formatted file size and URL
        $assets->getCollection()->transform(function ($asset) {
            $asset->file_size_formatted = $asset->file_size_formatted;
            $asset->url = Storage::url($asset->file_path);
            return $asset;
        });

        return response()->json($assets);
    }

    /**
     * Upload a new certificate asset.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,svg', 'max:5120'], // 5MB max
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('file');
        $clientId = auth()->user()->client_id;

        // Generate unique filename
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs("certificate-assets/{$clientId}", $filename, 'public');

        $asset = CertificateAsset::create([
            'client_id' => $clientId,
            'name' => $request->input('name', $file->getClientOriginalName()),
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);

        $asset->url = Storage::url($asset->file_path);

        return response()->json([
            'message' => 'Asset uploaded successfully.',
            'data' => $asset,
        ], 201);
    }

    /**
     * Get a specific asset.
     */
    public function show(CertificateAsset $certificateAsset): JsonResponse
    {
        $certificateAsset->url = Storage::url($certificateAsset->file_path);
        $certificateAsset->file_size_formatted = $certificateAsset->file_size_formatted;

        return response()->json([
            'data' => $certificateAsset,
        ]);
    }

    /**
     * Delete a certificate asset.
     */
    public function destroy(CertificateAsset $certificateAsset): JsonResponse
    {
        // Delete the file from storage
        if (Storage::exists($certificateAsset->file_path)) {
            Storage::delete($certificateAsset->file_path);
        }

        $certificateAsset->delete();

        return response()->json([
            'message' => 'Asset deleted successfully.',
        ]);
    }
}
