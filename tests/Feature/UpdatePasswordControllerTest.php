<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Javaabu\Auth\Tests\InteractsWithDatabase;
use Javaabu\Auth\Tests\TestCase;

class UpdatePasswordControllerTest extends TestCase
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
    public function it_redirects_to_the_password_update_page_if_the_user_is_required_to_update_the_password()
    {
        $user = $this->getUser('user@example.com');

        $user->require_password_update = true;
        $user->save();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'require_password_update' => true,
        ]);

        $this->actingAs($user, 'web');

        $this->get('')
            ->assertRedirect('/password/update');
    }

    /** @test */
    public function it_redirects_to_the_dashboard_if_password_update_is_not_required()
    {
        $user = $this->getUser('user@example.com');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'require_password_update' => false,
        ]);

        $this->actingAs($user, 'web');

        $this->get('/password/update')
            ->assertRedirect('')
            ->assertDontSee('Update Password');
    }

    /** @test */
    public function it_can_display_the_password_update_page()
    {
        $user = $this->getUser('user@example.com');

        $user->require_password_update = true;
        $user->save();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'require_password_update' => true,
        ]);

        $this->actingAs($user, 'web');

        $this->get('/password/update')
            ->assertStatus(200)
            ->assertSee('Update Password');
    }

    /** @test */
    public function it_does_not_allow_the_password_to_be_updated_using_an_invalid_current_password()
    {
        $user = $this->getUser('user@example.com');

        $user->require_password_update = true;
        $user->save();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'require_password_update' => true,
        ]);

        $this->assertTrue(Hash::check('password', $user->password), 'Invalid password');

        $this->actingAs($user, 'web');

        $this->post('/password/update', [
            'current_password' => '12312312',
            'password' => 'abc12345',
            'password_confirmation' => 'abc12345',
        ])
            ->assertSessionHasErrors('current_password')
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'require_password_update' => true,
        ]);

        $user = $user->fresh();
        $this->assertFalse(Hash::check('abc12345', $user->password), 'Invalid password');
    }

    /** @test */
    public function it_does_not_allow_the_password_to_be_updated_using_a_same_password_as_the_current_password()
    {
        $user = $this->getUser('user@example.com');

        $user->require_password_update = true;
        $user->save();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'require_password_update' => true,
        ]);

        $this->assertTrue(Hash::check('password', $user->password), 'Invalid password');

        $this->actingAs($user, 'web');

        $this->post('/password/update', [
            'current_password' => 'password',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
            ->assertSessionHasErrors('password')
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'require_password_update' => true,
        ]);
    }

    /** @test */
    public function it_requires_the_new_password_to_be_confirmed_when_updating_the_password()
    {
        $user = $this->getUser('user@example.com');

        $user->require_password_update = true;
        $user->save();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'require_password_update' => true,
        ]);

        $this->assertTrue(Hash::check('password', $user->password), 'Invalid password');

        $this->actingAs($user, 'web');

        $this->post('/password/update', [
            'current_password' => 'password',
            'password' => 'abc12345',
            'password_confirmation' => 'abc1234',
        ])
            ->assertSessionHasErrors('password')
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'require_password_update' => true,
        ]);

        $user = $user->fresh();
        $this->assertFalse(Hash::check('abc12345', $user->password), 'Invalid password');
    }

    /** @test */
    public function it_can_update_the_password()
    {
        $user = $this->getUser('user@example.com');

        $user->require_password_update = true;
        $user->save();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'require_password_update' => true,
        ]);

        $this->assertTrue(Hash::check('password', $user->password), 'Invalid password');

        $this->actingAs($user, 'web');

        $this->post('/password/update', [
            'current_password' => 'password',
            'password' => 'abc12345',
            'password_confirmation' => 'abc12345',
        ])
            ->assertSessionMissing('errors')
            ->assertRedirect('');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'require_password_update' => false,
        ]);

        $user = $user->fresh();
        $this->assertTrue(Hash::check('abc12345', $user->password), 'Invalid password');
    }
}
