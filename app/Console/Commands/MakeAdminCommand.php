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
#[Signature('portfolio:make-admin {--email= : Admin email} {--name= : Display name}')]
#[Description('Create (or promote) the admin user for the Filament panel')]
class MakeAdminCommand extends Command
{
    public function handle(): int
    {
        $email = $this->option('email') ?: text('Email', required: true, validate: ['email' => 'email']);
        $name = $this->option('name') ?: text('Name', default: 'Omar Khaled', required: true);

        $user = User::query()->where('email', $email)->first();

        if ($user) {
            $user->forceFill(['is_admin' => true])->save();
            $this->components->info("{$email} is now an admin. Set up MFA on first login.");

            return self::SUCCESS;
        }

        $password = password('Password (min. 12 characters)', required: true, validate: fn (string $value) => Validator::make(
            ['password' => $value],
            ['password' => ['required', 'string', 'min:12']],
        )->errors()->first('password') ?: null);

        User::query()->create(['name' => $name, 'email' => $email, 'password' => $password])
            ->forceFill(['is_admin' => true, 'email_verified_at' => now()])
            ->save();

        $this->components->info("Admin {$email} created. You'll be asked to set up an authenticator app on first login.");

        return self::SUCCESS;
    }
}
