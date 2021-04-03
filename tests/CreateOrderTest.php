<?php

use App\Enums\OrderStatus;
use App\Enums\Role;
use App\Models\Order;
use App\Models\Pet;
use App\Models\User;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class CreateOrderTest extends TestCase
{
    use DatabaseMigrations;

    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();
    }

    public function test_admin_can_place_an_order()
    {
        $admin = User::factory()->create(['role' => Role::STORE_ADMIN]);

        $this->actingAs($admin);

        $pet = Pet::factory()->create();

        $faker = $this->faker;

        $newOrderData = [
            'petId' => $pet->id,
            'quantity' => rand(1, 20),
            'shipDate' => $faker->dateTime(),
            'status' => OrderStatus::PLACED,
            'complete' => false,
        ];

        $response = $this->call('post', '/store/order', $newOrderData);

        $this->assertEquals(200, $response->status());
        $this->assertEquals(1, Order::count());
        $this->seeInDatabase('orders', ['pet_id' => $pet->id]);
    }
}
