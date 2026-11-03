<?php

namespace App\Console\Commands;

use App\Models\SecurityEvent;
use App\Models\User;
use App\Services\LoginGuard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/**
 * Creates the first admin from the ADMIN_EMAIL and ADMIN_PASSWORD settings when the server starts, for
 * hosts without a command line (such as Render's free plan). It never changes an existing account's
 * password, so the setting can stay or be removed after the first start, unless ADMIN_RESET_PASSWORD
 * is turned on: then the account's password is set to ADMIN_PASSWORD (for a forgotten password).
 */
class EnsureAdmin extends Command
{
    protected $signature = 'matako:ensure-admin';

    protected $description = 'Create the admin account from ADMIN_EMAIL / ADMIN_PASSWORD if it does not exist yet';

    public function handle(): int
    {
        $email = Str::lower(trim((string) env('ADMIN_EMAIL', '')));
        $password = (string) env('ADMIN_PASSWORD', '');
        if ($email === '') {
            $this->line('ADMIN_EMAIL is not set; no admin account created.');

            return self::SUCCESS;
        }

        $user = User::where('email', $email)->first();
        $reset = filter_var(env('ADMIN_RESET_PASSWORD', false), FILTER_VALIDATE_BOOL);
        if ($user && ! $reset) {
            if (! $user->is_admin) {
                $user->forceFill(['is_admin' => true, 'disabled_at' => null])->save();
                $this->info("{$email} is now an admin.");
            } else {
                $this->line("Admin {$email} already exists; password left unchanged.");
            }

            return self::SUCCESS;
        }

        $rules = ['email' => ['required', 'email'], 'password' => ['required', Password::min(12)->mixedCase()->numbers()->symbols()]];
        $validator = Validator::make(['email' => $email, 'password' => $password], $rules);
        if ($validator->fails()) {
            // Never print the password itself.
            $this->error('Admin not created: '.implode(' ', $validator->errors()->all()));

            return self::FAILURE;
        }

        if ($user) {
            // Password recovery: new password, admin access back, lockout lifted, every other session signed out.
            $user->forceFill(['password' => Hash::make($password), 'is_admin' => true, 'disabled_at' => null])->setRememberToken(Str::random(60));
            $user->save();
            $user->tokens()->delete();
            app(LoginGuard::class)->succeeded($email);
            SecurityEvent::record('admin.password_reset', $email, $user, 'Reset from the ADMIN_RESET_PASSWORD setting');
            $this->warn("Password reset for {$email}. Remove ADMIN_RESET_PASSWORD now, or it resets again on every restart.");

            return self::SUCCESS;
        }

        $user = new User([
            'name' => (string) env('ADMIN_NAME', 'MataKo Admin'),
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'professional',
        ]);
        $user->is_admin = true;
        $user->save();
        $this->info("Admin account created for {$email}.");

        return self::SUCCESS;
    }
}
