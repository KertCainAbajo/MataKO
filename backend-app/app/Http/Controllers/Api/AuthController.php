<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use App\Models\User;
use App\Services\GoogleIdTokenVerifier;
use App\Services\LoginGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class AuthController extends Controller
{
    /** App sign-ins expire after this many days, so a lost phone or copied token stops working. */
    public const TOKEN_DAYS = 30;

    public function register(Request $request): JsonResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email', '')))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'age' => ['required', 'integer', 'min:18', 'max:120'],
            'role' => ['required', 'in:student,professional'],
        ]);

        // Always hash passwords before saving them.
        $data['password'] = Hash::make($data['password']);
        unset($data['password_confirmation']);
        $user = User::create($data);
        $user->forceFill(['last_login_at' => now()])->save();
        $token = $user->createToken('mobile-app', ['*'], now()->addDays(self::TOKEN_DAYS))->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function login(Request $request, LoginGuard $guard): JsonResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email', '')))]);

        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $guard->ensureNotLocked($data['email']);

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            $guard->failed($data['email']);
            throw ValidationException::withMessages(['email' => [__('The provided credentials are incorrect.')]]);
        }
        $guard->succeeded($data['email']);
        if ($user->isDisabled()) {
            throw ValidationException::withMessages(['email' => [__('This account has been disabled. Please contact the MataKo team.')]]);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('mobile-app', ['*'], now()->addDays(self::TOKEN_DAYS))->plainTextToken,
        ]);
    }

    /**
     * Sign in with a Google ID token from the app. An account with the same verified email is linked.
     * A new person is asked once for the details MataKo needs (age, user type, phone) before an
     * account is created; the app then sends the same token again with those details.
     */
    public function google(Request $request, GoogleIdTokenVerifier $verifier): JsonResponse
    {
        $request->validate(['id_token' => ['required', 'string', 'max:4096']]);

        try {
            $google = $verifier->verify($request->input('id_token'));
        } catch (RuntimeException $exception) {
            SecurityEvent::record('google.failed', details: $exception->getMessage());
            throw ValidationException::withMessages(['id_token' => [__($exception->getMessage())]]);
        }

        $user = User::where('google_id', $google['sub'])->first() ?? User::where('email', $google['email'])->first();

        if (! $user) {
            if (! $request->hasAny(['age', 'role', 'phone'])) {
                return response()->json(['needs_profile' => true, 'name' => $google['name'], 'email' => $google['email']]);
            }

            $data = $request->validate([
                'name' => ['nullable', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:20'],
                'age' => ['required', 'integer', 'min:18', 'max:120'],
                'role' => ['required', 'in:student,professional'],
            ]);
            $user = User::create([
                ...$data,
                'name' => ($data['name'] ?? null) ?: $google['name'],
                'email' => $google['email'],
                // Google accounts sign in without a MataKo password; this random one is never shown.
                'password' => Hash::make(Str::random(40)),
            ]);
            $status = 201;
        }

        if ($user->isDisabled()) {
            throw ValidationException::withMessages(['email' => [__('This account has been disabled. Please contact the MataKo team.')]]);
        }

        $user->forceFill(['google_id' => $google['sub'], 'last_login_at' => now()])->save();

        return response()->json([
            'user' => $user->fresh(),
            'token' => $user->createToken('mobile-app', ['*'], now()->addDays(self::TOKEN_DAYS))->plainTextToken,
        ], $status ?? 200);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }

    public function updateUser(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'age' => ['required', 'integer', 'min:18', 'max:120'],
            'role' => ['required', 'in:student,professional'],
        ]);

        $user->update($data);

        return response()->json(['user' => $user->fresh()]);
    }

    public function logout(Request $request): JsonResponse
    {
        // Revoke only the token used for this request, leaving other signed-in devices active.
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => __('Logged out successfully.')]);
    }
}
