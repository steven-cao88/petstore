<?php

use App\Models\User;
use Carbon\Carbon;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AuthenticateUserTest extends TestCase
{
    use DatabaseMigrations;

    public function test_user_can_login()
    {
        $loginDetails = [
            'username' => 'test',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        ];

        $user = User::factory()->create($loginDetails);

        $this->notSeeInDatabase('api_tokens', ['user_id' => $user->id]);

        $response = $this->call('get', '/user/login', $loginDetails);

        $this->assertEquals(200, $response->status());

        $token = $user->apiTokens->first();

        $this->seeJson([
            'api_token' => $token->value
        ]);
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $existingToken = $user->createApiToken();

        $response = $this->call('get', '/user/logout', [
            'api_token' => $existingToken->value
        ]);

        $this->assertEquals(200, $response->status());

        $this->notSeeInDatabase('api_tokens', ['value' => $existingToken->value]);
    }

    public function test_user_can_not_logout_as_other_user()
    {
        $user1 = User::factory()->create();

        $this->actingAs($user1);

        $user2 = User::factory()->create();
        $existingToken = $user2->createApiToken();

        $response = $this->call('get', '/user/logout', [
            'api_token' => $existingToken->value
        ]);

        $this->assertEquals(401, $response->status());

        $this->seeInDatabase('api_tokens', ['value' => $existingToken->value]);
    }

    public function test_user_can_not_use_expired_api_token()
    {
        $user = User::factory()->create();

        $apiToken = $user->createApiToken();
        $apiToken->expired_at = Carbon::now()->subMinutes(10);
        $apiToken->save();

        $response = $this->call('get', "/user/{$user->username}", [
            'api_token' => $apiToken->value
        ]);

        $this->assertEquals(401, $response->status());
    }
}
