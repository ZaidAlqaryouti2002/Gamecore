<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends AdminResourceController
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate(['category_id' => ['nullable', 'integer']]);
        $query = Product::query()->with('category:id,name');
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $this->listing($request, $query, ['name', 'sku', 'brand']);
    }

    public function show(Product $product): JsonResponse
    {
        return $this->data($product->load('category:id,name'));
    }

    public function store(Request $request): JsonResponse
    {
        $product = new Product;
        $product->forceFill($this->validated($request, $product))->save();

        return $this->data($product, 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $product->forceFill($this->validated($request, $product))->save();

        return $this->data($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        if ($product->orderItems()->exists()) {
            return response()->json(['message' => 'Products used in orders cannot be deleted. Deactivate this product instead.'], 409);
        }

        return $this->remove($product);
    }

    private function validated(Request $request, Product $product): array
    {
        return $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($product)],
            'description' => ['required', 'string', 'max:10000'],
            'brand' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'between:0,99999999.99'],
            'sale_price' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'lt:price'],
            'stock' => ['required', 'integer', 'between:0,4294967295'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
