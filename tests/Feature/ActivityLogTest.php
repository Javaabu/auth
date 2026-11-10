<?php

namespace Javaabu\Auth\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Javaabu\Auth\Support\ActivityLogger;
use Javaabu\Auth\Tests\Models\User;
use Javaabu\Auth\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ActivityLogTest extends TestCase
{
    #[Test]
    public function it_logs_logins_and_failed_logins_when_activitylog_is_installed(): void
    {
        if (! function_exists('activity')) {
            $this->assertFalse(ActivityLogger::isAvailable());

            return;
        }

        $user = User::factory()->create(['email' => 'ahmed@example.com']);

        $this->post('/login', ['email' => 'ahmed@example.com', 'password' => 'wrong']);
        $this->post('/login', ['email' => 'ahmed@example.com', 'password' => 'password']);

        $this->assertDatabaseHas('activity_log', ['description' => 'failed_login', 'causer_id' => $user->id, 'causer_type' => $user->getMorphClass()]);
        $this->assertDatabaseHas('activity_log', ['description' => 'login', 'causer_id' => $user->id]);
    }

    #[Test]
    public function it_logs_nothing_when_turned_off(): void
    {
        config()->set('javaabu-auth.log_activity', false);

        User::factory()->create(['email' => 'ahmed@example.com']);

        $this->post('/login', ['email' => 'ahmed@example.com', 'password' => 'password']);

        $this->assertEquals(0, DB::table('activity_log')->count());
    }

    #[Test]
    public function logins_work_without_activitylog(): void
    {
        User::factory()->create(['email' => 'ahmed@example.com']);

        $this->post('/login', ['email' => 'ahmed@example.com', 'password' => 'password'])
            ->assertRedirect('/dashboard');
    }
}
