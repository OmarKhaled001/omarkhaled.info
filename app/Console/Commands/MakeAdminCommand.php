<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/** The only way to create an admin: credentials are typed, never stored in code or seeders. */
#[Signature('portfolio:make-admin {--email= : Admin email} {--name= : Display name} {--reset-password : Set a new password for an existing admin} {--reset-mfa : Remove the authenticator app so it can be set up again}')]
#[Description('Create (or promote) the admin user for the Filament panel, or reset its password / MFA')]
class MakeAdminCommand extends Command
{
    public function handle(): int
    {
        $email = $this->option('email') ?: text('Email', required: true, validate: ['email' => 'email']);
        $user = User::query()->where('email', $email)->first();

        if ($user) {
            $user->forceFill(['is_admin' => true]);

            if ($this->option('reset-password')) {
                $user->forceFill(['password' => $this->askPassword()]);
            }

            if ($this->option('reset-mfa')) {
                // For a lost phone: the next login asks to scan a new QR code.
                $user->forceFill(['app_authentication_secret' => null, 'app_authentication_recovery_codes' => null]);
            }

            $user->save();

            $this->components->info(match (true) {
                $this->option('reset-password') && $this->option('reset-mfa') => "Password and authenticator reset for {$email}.",
                (bool) $this->option('reset-password') => "Password updated for {$email}.",
                (bool) $this->option('reset-mfa') => "Authenticator removed for {$email}; set it up again at next login.",
                default => "{$email} is already an admin. Use --reset-password to set a new password.",
            });

            return self::SUCCESS;
        }

        $name = $this->option('name') ?: text('Name', default: 'Omar Khaled', required: true);

        User::query()->create(['name' => $name, 'email' => $email, 'password' => $this->askPassword()])
            ->forceFill(['is_admin' => true, 'email_verified_at' => now()])
            ->save();

        $this->components->info("Admin {$email} created. You'll be asked to set up an authenticator app on first login.");

        return self::SUCCESS;
    }

    private function askPassword(): string
    {
        return password('Password (min. 12 characters)', required: true, validate: fn (string $value) => Validator::make(
            ['password' => $value],
            ['password' => ['required', 'string', 'min:12']],
        )->errors()->first('password') ?: null);
    }
}
