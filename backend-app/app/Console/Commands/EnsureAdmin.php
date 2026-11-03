<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/**
 * Creates the first admin from the ADMIN_EMAIL and ADMIN_PASSWORD settings when the server starts, for
 * hosts without a command line (such as Render's free plan). It never changes an existing account's
 * password, so the setting can stay or be removed after the first start.
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
        if ($user) {
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
