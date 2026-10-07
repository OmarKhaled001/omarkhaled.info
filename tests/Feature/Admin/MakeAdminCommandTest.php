<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('creates an admin with a prompted password', function () {
    $this->artisan('portfolio:make-admin', ['--email' => 'owner@omarkhaled.test', '--name' => 'Owner'])
        ->expectsQuestion('Password (min. 12 characters)', 'correct-horse-battery')
        ->assertSuccessful();

    $user = User::query()->where('email', 'owner@omarkhaled.test')->sole();
    expect($user->is_admin)->toBeTrue()->and(Hash::check('correct-horse-battery', $user->password))->toBeTrue();
});

it('resets the password and MFA of an existing admin', function () {
    $user = User::factory()->create(['email' => 'owner@omarkhaled.test']);
    $user->forceFill(['is_admin' => true, 'app_authentication_secret' => 'JBSWY3DPEHPK3PXP'])->save();

    $this->artisan('portfolio:make-admin', ['--email' => 'owner@omarkhaled.test', '--reset-password' => true, '--reset-mfa' => true])
        ->expectsQuestion('Password (min. 12 characters)', 'a-brand-new-passphrase')
        ->assertSuccessful();

    $user->refresh();
    expect(Hash::check('a-brand-new-passphrase', $user->password))->toBeTrue()
        ->and($user->app_authentication_secret)->toBeNull();
});
