<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->isSystemAdmin()) {
            return $this->error('Forbidden.', 403);
        }

        $query = User::with('department');

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        return $this->success($query->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        if (! $request->user()->isSystemAdmin()) {
            return $this->error('Forbidden.', 403);
        }

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email',
            'password'      => 'required|string|min:8|confirmed',
            'user_type'     => 'required|in:system_administrator,employee,scanner',
            'department_id' => [
                Rule::requiredIf(fn () => in_array($request->input('user_type'), ['employee', 'scanner'])),
                'nullable',
                'exists:departments,id',
            ],
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        // Only admin role clears department and permissions
        if ($validated['user_type'] === 'system_administrator') {
            $validated['department_id'] = null;
            $validated['permissions']   = null;
        } else {
            $validated['permissions'] = $validated['permissions'] ?? [];
        }

        $user = User::create($validated);
        $user->load('department');

        return $this->created($user);
    }

    public function show(User $user, Request $request): JsonResponse
    {
        if (! $request->user()->isSystemAdmin()) {
            return $this->error('Forbidden.', 403);
        }

        $user->load('department');

        return $this->success($user);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        if (! $request->user()->isSystemAdmin()) {
            return $this->error('Forbidden.', 403);
        }

        $validated = $request->validate([
            'name'          => 'sometimes|required|string|max:255',
            'email'         => ['sometimes', 'required', 'email', 'max:255',
                                Rule::unique('users', 'email')->ignore($user->id)],
            'password'      => 'nullable|string|min:8|confirmed',
            'user_type'     => 'sometimes|required|in:system_administrator,employee,scanner',
            'department_id' => 'nullable|exists:departments,id',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        // Don't update password if not provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        // Only switching to admin clears department and permissions
        $newType = $validated['user_type'] ?? $user->user_type;
        if ($newType === 'system_administrator') {
            $validated['department_id'] = null;
            $validated['permissions']   = null;
        } elseif (array_key_exists('permissions', $validated)) {
            $validated['permissions'] = $validated['permissions'] ?? [];
        }

        $user->update($validated);
        $user->load('department');

        return $this->success($user, 'User updated successfully');
    }

    public function destroy(User $user, Request $request): JsonResponse
    {
        if (! $request->user()->isSystemAdmin()) {
            return $this->error('Forbidden.', 403);
        }

        if ($user->id === $request->user()->id) {
            return $this->error('You cannot delete your own account.', 422);
        }

        $user->tokens()->delete();
        $user->delete();

        return $this->success(null, 'User deleted successfully');
    }
}
