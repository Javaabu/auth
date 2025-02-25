<?php

namespace Javaabu\Auth\Tests\Feature\Rules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Auth\Rules\UniqueGmail;
use Javaabu\Auth\Tests\Feature\Models\User;
use Javaabu\Auth\Tests\InteractsWithDatabase;
use Javaabu\Auth\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UniqueGmailRuleTest extends TestCase
{
    use InteractsWithDatabase;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
    }

    #[Test]
    public function it_fails_if_there_is_a_matching_gmail()
    {
        $user_1 = User::factory()
            ->create([
                'name' => 'Ahmed',
                'email' => 'ahmed@gmail.com',
            ]);

        $user_2 = User::factory()
            ->create([
                'name' => 'Aisha',
                'email' => 'aisha@gmail.com',
            ]);

        $rule = new UniqueGmail(User::class);
        $passed = $this->checkRule($rule, 'email', 'ahmed+ali@gmail.com');

        $this->assertFalse($passed);
    }

    #[Test]
    public function it_can_specify_custom_field_name()
    {
        $user_1 = User::factory()
            ->create([
                'name' => 'Ahmed',
                'email' => 'ahmed@gmail.com',
            ]);

        $user_2 = User::factory()
            ->create([
                'name' => 'Aisha',
                'email' => 'aisha@gmail.com',
            ]);

        $rule = new UniqueGmail(User::class, 'email');
        $passed = $this->checkRule($rule, 'name', 'ahmed@gmail.com');

        $this->assertFalse($passed);
    }

    #[Test]
    public function it_can_ignore_user_ids()
    {
        $user_1 = User::factory()
            ->create([
                'name' => 'Ahmed',
                'email' => 'ahmed@gmail.com',
            ]);

        $user_2 = User::factory()
            ->create([
                'name' => 'Aisha',
                'email' => 'aisha@gmail.com',
            ]);

        $rule = (new UniqueGmail(User::class))->ignore($user_1);
        $passed = $this->checkRule($rule, 'email', 'ahmed@gmail.com');

        $this->assertTrue($passed);
    }

    #[Test]
    public function it_can_ignore_user_custom_user_ids()
    {
        $user_1 = User::factory()
            ->create([
                'name' => 'Ahmed',
                'email' => 'ahmed@gmail.com',
            ]);

        $user_2 = User::factory()
            ->create([
                'name' => 'Aisha',
                'email' => 'aisha@gmail.com',
            ]);

        $rule = (new UniqueGmail(User::class))->ignore('Ahmed', 'name');
        $passed = $this->checkRule($rule, 'email', 'ahmed@gmail.com');

        $this->assertTrue($passed);
    }

    #[Test]
    public function it_passes_if_there_is_no_matching_gmail()
    {
        $user_1 = User::factory()
            ->create([
                'name' => 'Ahmed',
                'email' => 'ahmed@gmail.com',
            ]);

        $user_2 = User::factory()
            ->create([
                'name' => 'Aisha',
                'email' => 'aisha@gmail.com',
            ]);

        $rule = new UniqueGmail(User::class);
        $passed = $this->checkRule($rule, 'email', 'mohemd+ali@gmail.com');

        $this->assertTrue($passed);
    }

    #[Test]
    public function it_passes_if_it_is_not_a_gmail()
    {
        $user_1 = User::factory()
            ->create([
                'name' => 'Ahmed',
                'email' => 'ahmed@gmail.com',
            ]);

        $user_2 = User::factory()
            ->create([
                'name' => 'Aisha',
                'email' => 'aisha@gmail.com',
            ]);

        $rule = new UniqueGmail(User::class);
        $passed = $this->checkRule($rule, 'email', 'ahmed@hotmail.com');

        $this->assertTrue($passed);
    }
}
