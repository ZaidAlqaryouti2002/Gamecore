<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends AdminResourceController
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate(['is_visible' => ['nullable', 'boolean']]);
        $query = Review::query()->with(['user:id,name', 'product:id,name']);
        if (isset($filters['is_visible'])) {
            $query->where('is_visible', $filters['is_visible']);
        }

        return $this->listing($request, $query, ['comment']);
    }

    public function show(Review $review): JsonResponse
    {
        return $this->data($review->load(['user:id,name', 'product:id,name']));
    }

    public function store(Request $request): JsonResponse
    {
        $review = new Review;
        $review->forceFill($this->validated($request))->save();

        return $this->data($review, 201);
    }

    public function update(Request $request, Review $review): JsonResponse
    {
        $review->forceFill($this->validated($request))->save();

        return $this->data($review);
    }

    public function destroy(Review $review): JsonResponse
    {
        return $this->remove($review);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'user')],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:5000'],
            'is_visible' => ['required', 'boolean'],
        ]);
    }
}
