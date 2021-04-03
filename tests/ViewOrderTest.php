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

class ViewOrderTest extends TestCase
{
    use DatabaseMigrations;

    public function test_admin_can_find_order_by_id()
    {
        $admin = User::factory()->create(['role' => Role::STORE_ADMIN]);

        $this->actingAs($admin);

        $pet = Pet::factory()
            ->for(Category::factory())
            ->has(Photo::factory()->count(2))
            ->has(Tag::factory()->count(3))
            ->create();

        $shipDate = Carbon::now()->addWeeks(1)->format('Y-m-d H:i:s.u');

        $order = Order::factory()
            ->for($pet)
            ->create([
                'quantity' => 1,
                'ship_date' => $shipDate,
                'status' => OrderStatus::PLACED,
                'complete' => false
            ]);

        $response = $this->call('GET', "/store/order/{$order->id}");

        $this->assertEquals(200, $response->status());

        $this->seeJson([
            'id' => 1,
            'petId' => $pet->id,
            'quantity' => 1,
            'shipDate' => $shipDate,
            'status' => OrderStatus::PLACED,
            'complete' => false
        ]);
    }
}
