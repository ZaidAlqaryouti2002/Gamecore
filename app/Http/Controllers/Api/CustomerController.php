<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CustomerController extends AdminResourceController
{
    public function index(Request $request): JsonResponse
    {
        return $this->listing($request, User::query()->where('role', 'user'), ['name', 'email']);
    }

    public function show(User $user): JsonResponse
    {
        $this->ensureCustomer($user);

        return $this->data($user);
    }

    public function store(Request $request): JsonResponse
    {
        $user = new User;
        $user->forceFill($this->validated($request, $user) + ['role' => 'user'])->save();

        return $this->data($user, 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->ensureCustomer($user);
        $fields = $this->validated($request, $user);
        if (empty($fields['password'])) {
            unset($fields['password']);
        }
        $user->forceFill($fields)->save();

        return $this->data($user);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->ensureCustomer($user);

        if ($user->orders()->exists()) {
            return response()->json(['message' => 'Customers with orders cannot be deleted. Deactivate this account instead.'], 409);
        }

        return $this->remove($user);
    }

    private function ensureCustomer(User $user): void
    {
        abort_unless($user->role === 'user', 404);
    }

    private function validated(Request $request, User $user): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['required', 'boolean'],
            'password' => [$user->exists ? 'nullable' : 'required', 'string', 'max:72', Password::min(8)->numbers()],
        ]);
    }
}
