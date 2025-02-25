<?php

namespace Javaabu\Auth\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Auth\Tests\Feature\Models\User;
use Javaabu\Auth\Tests\InteractsWithDatabase;
use Javaabu\Auth\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DetectsGmailAliasesTest extends TestCase
{
    use InteractsWithDatabase;
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
    }

    #[Test]
    public function it_can_detect_exact_same_gmail()
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

        $users = User::hasGmailAlias('ahmed@gmail.com')->get();

        $this->assertEquals(1, $users->count());
        $this->assertEquals($user_1->id, $users->first()->id);
    }

    #[Test]
    public function it_can_detect_plus_variation_in_db()
    {
        $user_1 = User::factory()
            ->create([
                'name' => 'Ahmed',
                'email' => 'ahmed+aisha@gmail.com',
            ]);

        $user_2 = User::factory()
            ->create([
                'name' => 'Aisha',
                'email' => 'aisha@gmail.com',
            ]);

        $users = User::hasGmailAlias('ahmed@gmail.com')->get();

        $this->assertEquals(1, $users->count());
        $this->assertEquals($user_1->id, $users->first()->id);
    }

    #[Test]
    public function it_can_detect_plus_variation_in_given_email()
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

        $users = User::hasGmailAlias('ahmed+aisha@gmail.com')->get();

        $this->assertEquals(1, $users->count());
        $this->assertEquals($user_1->id, $users->first()->id);
    }

    #[Test]
    public function it_can_detect_plus_variation_in_given_email_and_db()
    {
        $user_1 = User::factory()
            ->create([
                'name' => 'Ahmed',
                'email' => 'ahmed+mohamed@gmail.com',
            ]);

        $user_2 = User::factory()
            ->create([
                'name' => 'Aisha',
                'email' => 'aisha@gmail.com',
            ]);

        $users = User::hasGmailAlias('ahmed+aisha@gmail.com')->get();

        $this->assertEquals(1, $users->count());
        $this->assertEquals($user_1->id, $users->first()->id);
    }

    #[Test]
    public function it_can_detect_dot_variation_in_db()
    {
        $user_1 = User::factory()
            ->create([
                'name' => 'Ahmed',
                'email' => 'ah.med@gmail.com',
            ]);

        $user_2 = User::factory()
            ->create([
                'name' => 'Aisha',
                'email' => 'aisha@gmail.com',
            ]);

        $users = User::hasGmailAlias('ahmed@gmail.com')->get();

        $this->assertEquals(1, $users->count());
        $this->assertEquals($user_1->id, $users->first()->id);
    }

    #[Test]
    public function it_can_detect_dot_variation_in_given_email()
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

        $users = User::hasGmailAlias('ahm.ed@gmail.com')->get();

        $this->assertEquals(1, $users->count());
        $this->assertEquals($user_1->id, $users->first()->id);
    }

    #[Test]
    public function it_can_detect_dot_variation_in_given_email_and_db()
    {
        $user_1 = User::factory()
            ->create([
                'name' => 'Ahmed',
                'email' => 'ah.me.d@gmail.com',
            ]);

        $user_2 = User::factory()
            ->create([
                'name' => 'Aisha',
                'email' => 'aisha@gmail.com',
            ]);

        $users = User::hasGmailAlias('ahm.ed@gmail.com')->get();

        $this->assertEquals(1, $users->count());
        $this->assertEquals($user_1->id, $users->first()->id);
    }

    #[Test]
    public function it_can_detect_combined_dot_and_plus_variation()
    {
        $user_1 = User::factory()
            ->create([
                'name' => 'Ahmed',
                'email' => 'ah.me.d@gmail.com',
            ]);

        $user_2 = User::factory()
            ->create([
                'name' => 'Aisha',
                'email' => 'aisha@gmail.com',
            ]);

        $users = User::hasGmailAlias('ahm.ed+aisha@gmail.com')->get();

        $this->assertEquals(1, $users->count());
        $this->assertEquals($user_1->id, $users->first()->id);
    }

    #[Test]
    public function it_can_handle_different_case_in_db()
    {
        $user_1 = User::factory()
            ->create([
                'name' => 'Ahmed',
                'email' => 'Ah.me.d@gmail.com',
            ]);

        $user_2 = User::factory()
            ->create([
                'name' => 'Aisha',
                'email' => 'aisha@gmail.com',
            ]);

        $users = User::hasGmailAlias('ahm.ed+aisha@gmail.com')->get();

        $this->assertEquals(1, $users->count());
        $this->assertEquals($user_1->id, $users->first()->id);
    }

    #[Test]
    public function it_can_handle_different_case_in_the_given_email()
    {
        $user_1 = User::factory()
            ->create([
                'name' => 'Ahmed',
                'email' => 'ah.me.d@gmail.com',
            ]);

        $user_2 = User::factory()
            ->create([
                'name' => 'Aisha',
                'email' => 'aisha@gmail.com',
            ]);

        $users = User::hasGmailAlias('ahm.eD+aisha@gmail.com')->get();

        $this->assertEquals(1, $users->count());
        $this->assertEquals($user_1->id, $users->first()->id);
    }

    #[Test]
    public function it_works_even_if_there_is_no_matching_gmail()
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

        $users = User::hasGmailAlias('mohamed+ahmed@gmail.com')->get();

        $this->assertEquals(0, $users->count());
    }

    #[Test]
    public function it_works_even_if_there_is_no_gmails_in_the_db()
    {
        $user_1 = User::factory()
            ->create([
                'name' => 'Ahmed',
                'email' => 'ahmed@hotmail.com',
            ]);

        $user_2 = User::factory()
            ->create([
                'name' => 'Aisha',
                'email' => 'aisha@hotmail.com',
            ]);

        $users = User::hasGmailAlias('ahmed@gmail.com')->get();

        $this->assertEquals(0, $users->count());
    }

    #[Test]
    public function it_works_even_if_there_is_no_gmails_in_the_given_email()
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

        $users = User::hasGmailAlias('ahmed@hotmail.com')->get();

        $this->assertEquals(0, $users->count());
    }
}
