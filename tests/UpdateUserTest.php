<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UpdateUserTest extends TestCase
{
    use DatabaseMigrations;

    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();
    }

    public function test_admin_can_update_single_account()
    {
        $admin = User::factory()->create(['role' => Role::STORE_ADMIN]);

        $existingUser = User::factory()->create();

        $this->actingAs($admin);

        $faker = $this->faker;

        $updatedUserData = [
            'username' => $faker->unique()->userName,
            'firstName' => $faker->firstName,
            'lastName' => $faker->lastName,
            'email' => $faker->unique()->safeEmail,
            'password' => $faker->password(10),
            'phone' => $faker->phoneNumber,
        ];

        $response = $this->call('put', "/user/{$existingUser->username}", $updatedUserData);

        $this->assertEquals(200, $response->status());

        $existingUser->refresh();

        foreach ($updatedUserData as $key => $value) {
            if ($key === 'password') {
                $this->assertTrue(Hash::check($value, $existingUser->$key));
            } else {
                $this->assertEquals($existingUser->$key, $value);
            }
        }
    }

    public function test_normal_user_can_update_their_account()
    {
        $existingUser = User::factory()->create();

        $this->actingAs($existingUser);

        $faker = $this->faker;

        $updatedUserData = [
            'username' => $faker->unique()->userName,
            'firstName' => $faker->firstName,
            'lastName' => $faker->lastName,
            'email' => $faker->unique()->safeEmail,
            'password' => $faker->password(10),
            'phone' => $faker->phoneNumber,
        ];

        $response = $this->call('put', "/user/{$existingUser->username}", $updatedUserData);

        $this->assertEquals(200, $response->status());

        $existingUser->refresh();

        foreach ($updatedUserData as $key => $value) {
            if ($key === 'password') {
                $this->assertTrue(Hash::check($value, $existingUser->$key));
            } else {
                $this->assertEquals($existingUser->$key, $value);
            }
        }
    }

    public function test_normal_user_can_not_update_other_account()
    {
        $user1 = User::factory()->create();

        $user2 = User::factory()->create();

        $this->actingAs($user1);

        $faker = $this->faker;

        $updatedUserData = [
            'username' => $faker->unique()->userName,
            'firstName' => $faker->firstName,
            'lastName' => $faker->lastName,
            'email' => 'john@apple.com',
            'password' => $faker->password(10),
            'phone' => $faker->phoneNumber,
        ];

        $response = $this->call('put', "/user/{$user2->username}", $updatedUserData);

        $this->assertEquals(403, $response->status());
        $this->notSeeInDatabase('users', ['email' => 'john@apple.com']);
    }
}
