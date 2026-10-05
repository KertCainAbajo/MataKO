<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class MakeAdmin extends Command
{
    protected $signature = 'matako:make-admin {email : Email address of the admin} {--name= : Name for a new account}';

    protected $description = 'Create an admin account for the MataKo dashboard, or give an existing account admin access';

    public function handle(): int
    {
        $email = Str::lower(trim($this->argument('email')));
        if (Validator::make(['email' => $email], ['email' => ['required', 'email']])->fails()) {
            $this->error('Enter a valid email address.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            $user->forceFill(['is_admin' => true, 'disabled_at' => null])->save();
            $this->info("{$email} can now sign in to the admin dashboard with their existing password.");

            return self::SUCCESS;
        }

        $name = $this->option('name') ?: text('Name', default: 'MataKo Admin', required: true);
        $secret = password('Password (at least 8 characters)', required: true);
        if (Validator::make(['password' => $secret], ['password' => [Password::min(8)]])->fails()) {
            $this->error('The password must be at least 8 characters.');

            return self::FAILURE;
        }
        if (password('Confirm password', required: true) !== $secret) {
            $this->error('The passwords do not match.');

            return self::FAILURE;
        }

        $user = new User(['name' => $name, 'email' => $email, 'password' => Hash::make($secret), 'role' => 'professional']);
        $user->is_admin = true;
        $user->save();

        $this->info("Admin account created for {$email}. Sign in at ".url('/admin'));

        return self::SUCCESS;
    }
}
