<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Product Controller
 * Manages subscription products
 */
class ProductController extends BaseController
{
    /**
     * GET /api/v1/billing/products
     */
    public function index(): JsonResponse
    {
        return $this->success(Product::all());
    }

    /**
     * POST /api/v1/billing/products
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $product = Product::create($validated);

        return $this->success($product, 'Product created successfully', 201);
    }

    /**
     * PUT /api/v1/billing/products/{id}
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $product->update($validated);

        return $this->success($product, 'Product updated successfully');
    }

    /**
     * DELETE /api/v1/billing/products/{id}
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return $this->success(null, 'Product deleted successfully');
    }
}