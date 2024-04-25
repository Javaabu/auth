<?php

namespace Javaabu\Auth\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Javaabu\Auth\Agent;
use Javaabu\Auth\Session\Session;
use Javaabu\Auth\Tests\InteractsWithDatabase;
use Javaabu\Auth\Tests\TestCase;

class MultiAuthDatabaseSessionHandlerTest extends TestCase
{
    use InteractsWithDatabase;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('session.driver', 'multi_auth_database');

        $this->runMigrations();
        $this->seedDefaultUsers();
    }

    /** @test */
    public function it_records_the_user_type_in_the_sessions_table(): void
    {
        $user = $this->getUser('user@example.com');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect('/');

        $session_id = session()->getId();

        $this->assertDatabaseHas('sessions', [
            'id' => $session_id,
            'user_id' => $user->id,
            'user_type' => $user->getMorphClass(),
        ]);
    }

    /** @test */
    public function it_can_record_session_data_even_when_not_logged_in(): void
    {
        $this->post('/session-test')
            ->assertSessionHas('test_key', 'test_value')
            ->assertSuccessful();

        $session_id = session()->getId();

        $this->assertDatabaseHas('sessions', [
            'id' => $session_id,
            'user_id' => null,
            'user_type' => null,
        ]);
    }

    /** @test */
    public function it_can_retrieve_the_sessions_of_a_user(): void
    {
        $user = $this->getUser('user@example.com');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect('/');

        $session_id = session()->getId();

        $last_session = $user->sessions()->first();

        $this->assertEquals($session_id, $last_session->id);
        $this->assertEquals($user->id, $last_session->user_id);
        $this->assertEquals($user->getMorphClass(), $last_session->user_type);
        $this->assertEquals('user@example.com', $last_session->user->email);
    }

    /** @test */
    public function it_knows_the_current_device(): void
    {
        $user = $this->getUser('user@example.com');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->withoutCookie('laravel_session');

        $current_session_id = session()->getId();

        /** @var Session $current_session */
        $current_session = $user->sessions()->whereId($current_session_id)->first();

        Model::unguard();

        $old_session = new Session($current_session->getAttributes());
        $old_session->id = $current_session->id . '123';
        $old_session->save();

        $this->assertEquals($current_session_id, $current_session->id);
        $this->assertTrue($current_session->is_current_device);

        $this->assertEquals($user->id, $current_session->user_id);
        $this->assertEquals($user->getMorphClass(), $current_session->user_type);

        $old_session = $user->sessions()->whereId($current_session_id . '123')->first();

        $this->assertEquals($current_session_id . '123', $old_session->id);
        $this->assertFalse($old_session->is_current_device);

        $this->assertEquals($user->id, $old_session->user_id);
        $this->assertEquals($user->getMorphClass(), $old_session->user_type);
    }

    /** @test */
    public function it_creates_the_session_user_agent(): void
    {
        $user = $this->getUser('user@example.com');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect('/');

        $session_id = session()->getId();

        $last_session = $user->sessions()->first();

        $this->assertEquals($session_id, $last_session->id);
        $this->assertEquals($user->id, $last_session->user_id);
        $this->assertEquals($user->getMorphClass(), $last_session->user_type);
        $this->assertInstanceOf(Agent::class, $last_session->agent);
    }

    /** @test */
    public function it_records_the_last_activity_time(): void
    {
        $user = $this->getUser('user@example.com');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect('/');

        $session_id = session()->getId();

        $last_session = $user->sessions()->first();

        $this->assertEquals($session_id, $last_session->id);
        $this->assertEquals($user->id, $last_session->user_id);
        $this->assertEquals($user->getMorphClass(), $last_session->user_type);
        $this->assertInstanceOf(Carbon::class, $last_session->last_activity);
    }

    /** @test */
    public function it_can_delete_other_sessions_of_the_user(): void
    {
        $user = $this->getUser('user@example.com');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->withoutCookie('laravel_session');

        $current_session_id = session()->getId();

        /** @var Session $current_session */
        $current_session = $user->sessions()->whereId($current_session_id)->first();

        Model::unguard();

        $old_session = new Session($current_session->getAttributes());
        $old_session->id = $current_session->id . '123';
        $old_session->save();

        $this->assertDatabaseHas('sessions', [
            'id' => $current_session_id,
            'user_id' => $user->id,
            'user_type' => $user->getMorphClass(),
        ]);

        $this->assertDatabaseHas('sessions', [
            'id' => $current_session->id . '123',
            'user_id' => $user->id,
            'user_type' => $user->getMorphClass(),
        ]);

        $user->deleteOtherSessionRecords();

        $this->assertDatabaseHas('sessions', [
            'id' => $current_session_id,
        ]);

        $this->assertDatabaseMissing('sessions', [
            'id' => $current_session->id . '123',
        ]);
    }

}
