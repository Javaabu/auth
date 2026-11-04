<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Support\Facades\Notification;
use Javaabu\Auth\Notifications\VerifyEmail;
use Javaabu\Auth\Tests\Models\Admin;
use Javaabu\Auth\Tests\Models\User;
use Javaabu\Auth\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ProfileTest extends TestCase
{
    #[Test]
    public function it_shows_the_profile_page(): void
    {
        $this->actingAs(User::factory()->create(['name' => 'އައިޝަތު ޒިޔާ']), 'web')
            ->get('/settings/profile')
            ->assertOk()
            ->assertSee('އައިޝަތު ޒިޔާ')
            ->assertSee('data-delete-account', false);
    }

    #[Test]
    public function it_updates_the_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->patch('/settings/profile', ['name' => 'ހަސަން ރަޝީދު', 'email' => $user->email])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'ހަސަން ރަޝީދު']);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    #[Test]
    public function changing_the_email_asks_for_verification_again(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->patch('/settings/profile', ['name' => $user->name, 'email' => 'new@example.com'])
            ->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertEquals('new@example.com', $user->email);
        $this->assertNull($user->email_verified_at);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    #[Test]
    public function the_email_must_be_unique_within_the_type_only(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'web_admin')
            ->patch('/admin/settings/profile', ['name' => $admin->name, 'email' => 'taken@example.com'])
            ->assertSessionHasNoErrors();

        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->patch('/settings/profile', ['name' => $user->name, 'email' => 'taken@example.com'])
            ->assertSessionHasErrors('email');
    }

    #[Test]
    public function it_deletes_the_account_with_the_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->delete('/settings/profile', ['password' => 'wrong'])
            ->assertSessionHasErrors('password');

        $this->delete('/settings/profile', ['password' => 'password'])
            ->assertRedirect('/login');

        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertGuest('web');
    }

    #[Test]
    public function types_without_account_deletion_cannot_delete(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'web_admin')
            ->delete('/admin/settings/profile', ['password' => 'password'])
            ->assertStatus(405);
    }
}
