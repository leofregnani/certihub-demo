<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! $user->active || ! Hash::check($data['password'], $user->password)) {
            $this->logger->log('auth.login_failed', 'Failed login attempt', null, ['email' => $data['email']], $user?->id);

            throw ValidationException::withMessages(['email' => 'Invalid credentials.']);
        }

        $token = $user->createToken('spa')->plainTextToken;
        $this->logger->log('auth.login', 'User logged in', $user, [], $user->id);

        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->logger->log('auth.logout', 'User logged out', $user);

        $token = $user->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink($data);
        $this->logger->log('auth.password_reset_requested', 'Password reset requested', null, ['email' => $data['email']]);

        // Same answer whether or not the e-mail exists (avoids account enumeration).
        return response()->json(['message' => 'If the e-mail exists, a reset link has been sent.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])->save();
                $user->tokens()->delete(); // sign out everywhere
                event(new PasswordReset($user));
                $this->logger->log('auth.password_reset', 'Password reset completed', $user, [], $user->id);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => 'This reset link is invalid or has expired.']);
        }

        return response()->json(['message' => 'Password updated.']);
    }
}
