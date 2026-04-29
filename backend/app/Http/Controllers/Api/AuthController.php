<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'locale' => ['nullable', Rule::in(['lv', 'en', 'ru', 'cs', 'de'])],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);

        $existing = User::where('email', $data['email'])->first();
        if ($existing) {
            // If the email exists but is not verified yet, allow re-registration by resending verification.
            if ($existing->hasVerifiedEmail()) {
                throw ValidationException::withMessages([
                    'email' => ['Email already used.'],
                ]);
            }

            $existing->forceFill([
                'name' => $data['name'],
                // Update password so the user can choose a new one while still unverified.
                'password' => Hash::make($data['password']),
                'locale' => $data['locale'] ?? $existing->locale ?? 'en',
                'phone' => $data['phone'] ?? $existing->phone ?? null,
            ])->save();

            $existing->sendEmailVerificationNotification();

            Auth::login($existing);
            $request->session()->regenerate();

            return response()->json([
                'user' => $existing,
                'message' => 'Email already registered but not verified yet. Verification email resent.',
            ], 201);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'locale' => $data['locale'] ?? 'en',
            'phone' => $data['phone'] ?? null,
            'role' => 'member',
            'rank' => 'Novice',
        ]);

        $user->sendEmailVerificationNotification();

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'user' => $user,
            'message' => 'Registration successful. Please check your email to verify your address.',
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($data, true)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $request->session()->regenerate();
        return response()->json(['user' => $request->user()]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->noContent();
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:32'],
            'locale' => ['required', Rule::in(['lv', 'en', 'ru', 'cs', 'de'])],
        ]);

        $user->update($data);

        return response()->json(['user' => $user->fresh()]);
    }

    public function deleteProfile(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;
        $registrationsDeleted = 0;
        $tokensDeleted = 0;
        $usersDeletedRows = 0;

        DB::transaction(function () use ($user, &$registrationsDeleted, &$tokensDeleted, &$usersDeletedRows): void {
            // Delete pivot/pivot-like rows.
            $registrationsDeleted = (int) $user->registrations()->delete();

            // Delete Sanctum tokens.
            $tokensDeleted = (int) $user->tokens()->delete();

            // Finally delete the user itself.
            // Use a query builder delete to get a concrete "affected rows" count.
            $usersDeletedRows = User::query()->whereKey($user->id)->delete();
        });

        $stillExists = User::query()->where('id', $userId)->exists();
        $deleted = ! $stillExists && $usersDeletedRows > 0;

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'deleted' => $deleted,
            'user_id' => $userId,
            'still_exists' => $stillExists,
            'registrations_deleted' => $registrationsDeleted,
            'tokens_deleted' => $tokensDeleted,
            'users_deleted_rows' => $usersDeletedRows,
        ]);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('auth.password')],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'remember_token' => Str::random(60),
        ])->save();

        return response()->json(['message' => __('passwords.reset')]);
    }

    public function verifyEmail(Request $request, int $id, string $hash)
    {
        $frontend = rtrim((string) env('FRONTEND_URL', ''), '/');
        // Fallback: avoid empty/relative redirects if FRONTEND_URL isn't configured.
        if ($frontend === '') {
            $frontend = rtrim((string) config('app.url', ''), '/');
        }

        if (!$request->hasValidSignature()) {
            return redirect($frontend . '/verify-email?status=invalid');
        }

        $user = User::find($id);
        if (!$user || !hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return redirect($frontend . '/verify-email?status=invalid');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect($frontend . '/verify-email?status=already');
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return redirect($frontend . '/verify-email?status=success');
    }

    public function resendVerification(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.']);
        }
        $user->sendEmailVerificationNotification();
        return response()->json(['message' => 'Verification email sent.']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (Throwable $e) {
            Log::error('Password reset link send failed', [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
                'exception' => $e::class,
            ]);

            // Avoid breaking the UI; don't reveal details to the user.
            $status = 'passwords.sent';
        }

        // Always respond with the same message to avoid email enumeration.
        return response()->json([
            'message' => __('passwords.sent'),
            'status' => $status,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json(['message' => __($status)]);
    }
}
