<?php

namespace Javaabu\Auth\Tests\Feature;

use Javaabu\Auth\Tests\Models\Admin;
use Javaabu\Auth\Tests\Models\User;
use Javaabu\Auth\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PasswordConfirmationTest extends TestCase
{
    #[Test]
    public function the_security_page_asks_for_the_password_first(): void
    {
        $this->actingAs(User::factory()->create(), 'web')
            ->get('/settings/security')
            ->assertRedirect('/confirm-password');
    }

    #[Test]
    public function it_confirms_the_password_and_continues(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')->get('/settings/security');

        $this->post('/confirm-password', ['password' => 'password'])
            ->assertRedirect('/settings/security');

        $this->get('/settings/security')->assertOk();
    }

    #[Test]
    public function it_rejects_a_wrong_password(): void
    {
        $this->actingAs(User::factory()->create(), 'web')
            ->post('/confirm-password', ['password' => 'wrong'])
            ->assertSessionHasErrors('password');
    }

    #[Test]
    public function confirming_as_a_user_does_not_confirm_as_an_admin(): void
    {
        $this->actingAs(User::factory()->create(), 'web')
            ->actingAs(Admin::factory()->create(), 'web_admin');

        $this->post('/confirm-password', ['password' => 'password']);

        $this->get('/settings/security')->assertOk();
        $this->get('/admin/settings/security')->assertRedirect('/admin/confirm-password');
    }
}
