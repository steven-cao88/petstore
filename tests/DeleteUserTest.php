<?php

use App\Enums\Role;
use App\Models\User;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class DeleteUserTest extends TestCase
{
    use DatabaseMigrations;

    public function test_admin_can_delete_single_account()
    {
        $admin = User::factory()->create(['role' => Role::STORE_ADMIN]);

        $existingUser = User::factory()->create(['username' => 'test']);

        $this->actingAs($admin);

        $response = $this->call('delete', "/user/{$existingUser->username}");

        $this->assertEquals(200, $response->status());

        $this->notSeeInDatabase('users', ['username' => 'test']);
    }

    public function test_normal_user_can_not_delete_other_account()
    {
        $user1 = User::factory()->create();

        $user2 = User::factory()->create(['email' => 'john@apple.com']);

        $this->actingAs($user1);

        $response = $this->call('delete', "/user/{$user2->username}");

        $this->assertEquals(403, $response->status());
        $this->seeInDatabase('users', ['email' => 'john@apple.com']);
    }
}
