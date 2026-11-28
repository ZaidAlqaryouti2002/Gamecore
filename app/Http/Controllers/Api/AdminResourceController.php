<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\ForeignKeyConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class AdminResourceController extends Controller
{
    protected function listing(Request $request, Builder $query, array $searchColumns): JsonResponse
    {
        $search = $request->validate(['search' => ['nullable', 'string', 'max:100']])['search'] ?? '';

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search, $searchColumns) {
                foreach ($searchColumns as $column) {
                    $query->orWhere($column, 'like', '%'.$search.'%');
                }
            });
        }

        return response()->json($query->latest('id')->paginate(15));
    }

    protected function data(Model $model, int $status = 200): JsonResponse
    {
        return response()->json(['data' => $model], $status);
    }

    protected function remove(Model $model): JsonResponse
    {
        try {
            $model->delete();
        } catch (ForeignKeyConstraintViolationException) {
            return response()->json(['message' => 'This record is linked to other records and cannot be deleted.'], 409);
        }

        return response()->json(null, 204);
    }
}
