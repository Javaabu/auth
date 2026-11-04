# Testing an app that uses javaabu/auth

```php
use Javaabu\Auth\Http\Middleware\RequirePassword;
use Javaabu\Auth\TwoFactor\TwoFactorProvider;
use Javaabu\Auth\UserTypes;
use PHPUnit\Framework\Attributes\Test;

#[Test]
public function admins_see_the_dashboard(): void
{
    $admin = Admin::factory()->create(['status' => 'approved', 'email_verified_at' => now()]);

    $this->actingAs($admin, 'web_admin')
        ->get('/admin')
        ->assertOk();
}

#[Test]
public function the_security_page_works_once_the_password_is_confirmed(): void
{
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'web_admin')
        ->withSession([RequirePassword::sessionKey(UserTypes::get('admin')) => time()])
        ->get('/admin/settings/security')
        ->assertOk();
}

#[Test]
public function admins_log_in_with_a_two_factor_code(): void
{
    $secret = 'JBSWY3DPEHPK3PXP';
    $admin = Admin::factory()->create([
        'two_factor_secret' => encrypt($secret),
        'two_factor_recovery_codes' => encrypt(json_encode(['code-1'])),
        'two_factor_confirmed_at' => now(),
    ]);

    $this->post('/admin/login', ['email' => $admin->email, 'password' => 'password'])
        ->assertRedirect('/admin/two-factor-challenge');

    $this->post('/admin/two-factor-challenge', ['code' => app(TwoFactorProvider::class)->currentCode($secret)])
        ->assertRedirect('/admin');

    $this->assertAuthenticatedAs($admin, 'web_admin');
}
```

- New users are `pending` by default (`HasAccountStatus`); set `status` to `approved` and `email_verified_at` in factories for users that should get through `auth.type`.
- With `max_login_attempts` set, tests that fail logins on purpose may lock the account; reset with `$user->resetLoginAttempts()`.
- Use Thaana in names and other user-facing data.
