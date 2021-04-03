<?php

use App\Enums\Role;
use App\Models\User;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class CreateUserTest extends TestCase
{
    use DatabaseMigrations;

    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();
    }

    public function test_admin_can_create_single_account()
    {
        $admin = User::factory()->create(['role' => Role::STORE_ADMIN]);

        $this->actingAs($admin);

        $faker = $this->faker;

        $newUserData = [
            'username' => $faker->unique()->userName,
            'firstName' => $faker->firstName,
            'lastName' => $faker->lastName,
            'email' => 'john@apple.com',
            'password' => $faker->password(10),
            'phone' => $faker->phoneNumber,
        ];

        $response = $this->call('post', '/user', $newUserData);

        $this->assertEquals(200, $response->status());
        $this->seeInDatabase('users', ['email' => 'john@apple.com']);
    }

    public function test_normal_user_can_not_create_single_account()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $faker = $this->faker;

        $newUserData = [
            'username' => $faker->unique()->userName,
            'firstName' => $faker->firstName,
            'lastName' => $faker->lastName,
            'email' => 'john@apple.com',
            'password' => $faker->password(10),
            'phone' => $faker->phoneNumber,
        ];

        $response = $this->call('post', '/user', $newUserData);

        $this->assertEquals(403, $response->status());
        $this->notSeeInDatabase('users', ['email' => 'john@apple.com']);
    }

    public function test_admin_can_create_multiple_accounts()
    {
        $admin = User::factory()->create(['role' => Role::STORE_ADMIN]);

        $this->actingAs($admin);

        $newUserList = [];

        $faker = $this->faker;

        for ($counter = 0; $counter < 5; $counter++) {
            $newUserData = [
                'username' => $faker->unique()->userName,
                'firstName' => $faker->firstName,
                'lastName' => $faker->lastName,
                'email' => "john_{$counter}@apple.com",
                'password' => $faker->password(10),
                'phone' => $faker->phoneNumber,
            ];

            $newUserList[] = $newUserData;
        }

        // tested with alias POST '/user/createWithArray' endpoint as well
        $response = $this->call('post', '/user/createWithArray', [
            'users' => $newUserList
        ]);

        $this->assertEquals(200, $response->status());

        for ($counter = 0; $counter < 5; $counter++) {
            $this->seeInDatabase('users', ['email' => "john_{$counter}@apple.com"]);
        }
    }
}
