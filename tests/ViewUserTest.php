<?php

use App\Enums\Role;
use App\Models\User;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ViewUserTest extends TestCase
{
    use DatabaseMigrations;

    public function test_account_owner_can_get_account_by_account_name()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->get("/user/{$user->username}")
            ->seeJson([
                'id' => $user->id,
                'username' => $user->username,
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'email' => $user->email,
                'password' => $user->password,
                'phone' => $user->phone,
                'userStatus' => $user->userStatus
            ]);
    }

    public function test_admin_can_get_account_by_account_name()
    {
        $admin = User::factory()->create(['role' => Role::STORE_ADMIN]);

        $this->actingAs($admin);

        $user = User::factory()->create();

        $this->get("/user/{$user->username}")
            ->seeJson([
                'id' => $user->id,
                'username' => $user->username,
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'email' => $user->email,
                'password' => $user->password,
                'phone' => $user->phone,
                'userStatus' => $user->userStatus
            ]);
    }

    public function test_user_can_not_view_other_account_by_account_name()
    {
        $user1 = User::factory()->create();

        $this->actingAs($user1);

        $user2 = User::factory()->create();

        $response = $this->call('GET', "/user/{$user2->username}");

        $this->assertEquals(403, $response->status());
    }
}
