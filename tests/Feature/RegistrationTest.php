<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Javaabu\Auth\Tests\Models\User;
use Javaabu\Auth\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RegistrationTest extends TestCase
{
    #[Test]
    public function it_shows_the_registration_page(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('data-auth-page="register"', false);
    }

    #[Test]
    public function it_registers_a_user_with_a_thaana_name_and_logs_them_in(): void
    {
        Event::fake([Registered::class]);

        $this->post('/register', [
            'name' => 'މުޙައްމަދު ނާޒިމް',
            'email' => 'Nazim@Example.com',
            'password' => 'a-long-password',
            'password_confirmation' => 'a-long-password',
        ])->assertRedirect('/dashboard');

        $user = User::where('email', 'nazim@example.com')->firstOrFail();

        $this->assertEquals('މުޙައްމަދު ނާޒިމް', $user->name);
        $this->assertAuthenticatedAs($user, 'web');

        Event::assertDispatched(Registered::class);
    }

    #[Test]
    public function it_validates_registration(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->post('/register', [
            'name' => '',
            'email' => 'taken@example.com',
            'password' => 'a-long-password',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertGuest('web');
    }

    #[Test]
    public function types_without_registration_have_no_registration_routes(): void
    {
        $this->get('/admin/register')->assertNotFound();
        $this->assertFalse($this->userType('admin')->hasRoute('register'));
    }
}
