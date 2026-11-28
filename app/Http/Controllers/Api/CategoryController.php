<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends AdminResourceController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Category::query()->orderBy('name')->get()]);
    }

    public function show(Category $category): JsonResponse
    {
        return $this->data($category);
    }

    public function store(Request $request): JsonResponse
    {
        $category = new Category;
        $category->forceFill($this->validated($request, $category))->save();

        return $this->data($category, 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $category->forceFill($this->validated($request, $category))->save();

        return $this->data($category);
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->products()->exists()) {
            return response()->json(['message' => 'Categories with products cannot be deleted. Move their products first.'], 409);
        }

        return $this->remove($category);
    }

    private function validated(Request $request, Category $category): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($category)],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
