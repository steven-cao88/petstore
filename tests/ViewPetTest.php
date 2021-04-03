<?php

use App\Enums\PetStatus;
use App\Enums\Role;
use App\Models\Category;
use App\Models\Pet;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ViewPetTest extends TestCase
{
    use DatabaseMigrations;

    public function test_admin_can_find_pet_by_id()
    {
        $admin = User::factory()->create(['role' => Role::STORE_ADMIN]);

        $this->actingAs($admin);

        $category = Category::factory()->create();

        $pet = Pet::factory()
            ->for($category)
            ->has(Photo::factory()->count(2))
            ->has(Tag::factory()->count(3))
            ->create();

        $this->get("/pet/{$pet->id}")
            ->seeJson([
                'id' => $pet->id,
                'name' => $pet->name,
                'status' => $pet->status,
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name
                ]
            ]);
    }

    public function test_admin_can_find_pet_by_status()
    {
        $admin = User::factory()->create(['role' => Role::STORE_ADMIN]);

        $this->actingAs($admin);

        $category = Category::factory()->create();

        $pet1 = Pet::factory(['status' => PetStatus::AVAILABLE])
            ->for($category)
            ->has(Photo::factory()->count(2))
            ->has(Tag::factory()->count(3))
            ->create();

        $pet2 = Pet::factory(['status' => PetStatus::PENDING])
            ->for($category)
            ->has(Photo::factory()->count(2))
            ->has(Tag::factory()->count(3))
            ->create();

        $response = $this->call('GET', '/pet/findByStatus', ['status' => [PetStatus::AVAILABLE]]);

        $response->assertJsonCount(1);

        $response->assertJson([
            ['name' => $pet1->name]
        ]);

        $response = $this->call('GET', '/pet/findByStatus', ['status' => [PetStatus::AVAILABLE, PetStatus::PENDING]]);

        $response->assertJsonCount(2);

        $response->assertJson([
            ['name' => $pet1->name],
            ['name' => $pet2->name],
        ]);
    }
}
