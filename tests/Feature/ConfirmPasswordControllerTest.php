<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Javaabu\Auth\Tests\InteractsWithDatabase;
use Javaabu\Auth\Tests\TestCase;

class ConfirmPasswordControllerTest extends TestCase
{
    use InteractsWithDatabase;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
        $this->seedDefaultUsers();
    }

    /** @test */
    public function it_redirects_to_the_password_confirmation_page_if_the_user_is_required_to_confirm_the_password()
    {
        $user = $this->getUser('user@example.com');

        $this->actingAs($user, 'web');

        $this->get('/test')
            ->assertRedirect('/password/confirm');
    }

    /** @test */
    public function it_can_display_the_password_confirmation_page()
    {
        $this->withoutExceptionHandling();
        $user = $this->getUser('user@example.com');

        $this->actingAs($user, 'web');

        $this->get('/password/confirm')
            ->assertStatus(200)
            ->assertSee('Confirm Password');
    }

    /** @test */
    public function it_does_not_allow_the_password_to_be_confirmed_using_an_invalid_current_password()
    {
        $user = $this->getUser('user@example.com');

        $this->assertTrue(Hash::check('password', $user->password), 'Invalid password');

        $this->actingAs($user, 'web');

        $this->post('/password/confirm', [
            'password' => '12312312',
        ])
            ->assertSessionHasErrors('password')
            ->assertSessionMissing('web.auth.password_confirmed_at')
            ->assertRedirect();
    }

    /** @test */
    public function it_can_confirm_the_password()
    {
        $this->withoutExceptionHandling();

        $user = $this->getUser('user@example.com');

        $this->assertTrue(Hash::check('password', $user->password), 'Invalid password');

        $this->actingAs($user, 'web');

        $this->followingRedirects()
             ->get('/test')
             ->assertStatus(200)
             ->assertSee('Confirm Password');

        $this->post("/password/confirm", [
            'password' => 'password',
        ])
            ->assertSessionMissing('errors')
            ->assertSessionHas('web.auth.password_confirmed_at')
            ->assertRedirect('/test');
    }
}
