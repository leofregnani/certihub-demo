<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * User management (admin only, enforced in routes/api.php).
 */
class UserController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(Request $request)
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return User::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%'.$request->string('search').'%';
                $query->where(fn ($q) => $q->whereLike('name', $term)->orWhereLike('email', $term));
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
            'active' => ['boolean'],
        ]);

        $user = User::create($data);
        $this->logger->log('user.created', "User {$user->email} created", $user, ['role' => $user->role]);

        return response()->json($user, 201);
    }

    public function show(User $user): User
    {
        return $user;
    }

    public function update(Request $request, User $user): User
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
            'active' => ['boolean'],
        ]);

        if ($user->is($request->user()) && ($data['role'] !== User::ROLE_ADMIN || ($data['active'] ?? true) === false)) {
            throw ValidationException::withMessages(['role' => 'You cannot remove your own admin access or deactivate yourself.']);
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        $changed = array_values(array_diff(array_keys($user->getChanges()), ['updated_at']));
        $this->logger->log('user.updated', "User {$user->email} updated", $user, ['fields' => $changed]);

        // A deactivated user or a password change ends all their sessions.
        if (! $user->active || in_array('password', $changed, true)) {
            $user->tokens()->delete();
        }

        return $user;
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->is($request->user())) {
            throw ValidationException::withMessages(['user' => 'You cannot delete your own account.']);
        }

        $this->logger->log('user.deleted', "User {$user->email} deleted", $user);
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
