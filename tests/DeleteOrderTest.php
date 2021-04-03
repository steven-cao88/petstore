<?php

use App\Enums\OrderStatus;
use App\Enums\Role;
use App\Models\Category;
use App\Models\Order;
use App\Models\Pet;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class DeleteOrderTest extends TestCase
{
    use DatabaseMigrations;

    public function test_admin_can_delete_single_order()
    {
        $admin = User::factory()->create(['role' => Role::STORE_ADMIN]);

        $this->actingAs($admin);

        $pet = Pet::factory()
            ->for(Category::factory())
            ->has(Photo::factory()->count(2))
            ->has(Tag::factory()->count(3))
            ->create();

        $order = Order::factory()
            ->for($pet)
            ->create([
                'quantity' => 1,
                'ship_date' => Carbon::now()->addWeeks(1),
                'status' => OrderStatus::PLACED,
                'complete' => false
            ]);

        $response = $this->call('delete', "/store/order/{$order->id}");

        $this->assertEquals(200, $response->status());

        $this->notSeeInDatabase('orders', ['id' => 1]);
    }
}
