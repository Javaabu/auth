<?php

namespace Javaabu\Auth\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Auth\Tests\Feature\Models\User;
use Javaabu\Auth\Tests\InteractsWithDatabase;
use Javaabu\Auth\Tests\TestCase;

class UserTest extends TestCase
{
    use InteractsWithDatabase;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
    }

    /** @test */
    public function it_can_search_users()
    {
        $user_1 = User::factory()
            ->create([
                'name' => 'John',
                'email' => 'user-1@example.com',
            ]);

        $user_2 = User::factory()
            ->create([
                'name' => 'Doe',
                'email' => 'user-2@example.com',
            ]);

        $users = User::search('John')->get();

        $this->assertEquals(1, $users->count());
        $this->assertEquals($user_1->id, $users->first()->id);
    }
}
