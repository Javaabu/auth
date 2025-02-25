<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Javaabu\Auth\Tests\InteractsWithDatabase;
use Javaabu\Auth\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Activitylog\Models\Activity;

class UserControllerTest extends TestCase
{
    use InteractsWithDatabase;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
        $this->seedDefaultUsers();
    }

    #[Test]
    public function it_records_the_password_changed_event()
    {
        $this->withoutExceptionHandling();

        $now = '2024-09-08 12:56:00';

        $this->travelTo($now);

        $user = $this->getUser('user@example.com');

        $this->assertTrue(Hash::check('password', $user->password), 'Invalid password');

        $this->actingAs($user);

        $this->patch('/account', [
            'action' => 'update_password',
            'current_password' => 'password',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect('/account');

        $user->fresh();
        $this->assertTrue(Hash::check('12345678', $user->password), 'Invalid password');

        /** @var Activity $log */
        $log = Activity::latest('id')->first();

        $this->assertDatabaseHas('activity_log', [
            'id' => $log->id,
            'description' => 'password_changed',
            'causer_type' => $user->getMorphClass(),
            'causer_id' => $user->id,
            'subject_type' => $user->getMorphClass(),
            'subject_id' => $user->id,
            'created_at' => $now,
        ]);
    }
}
