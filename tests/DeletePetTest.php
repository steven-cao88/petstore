<?php

use App\Enums\Role;
use App\Models\Category;
use App\Models\Pet;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class DeletePetTest extends TestCase
{
    use DatabaseMigrations;

    public function test_admin_can_delete_single_pet()
    {
        $admin = User::factory()->create(['role' => Role::STORE_ADMIN]);

        $this->actingAs($admin);

        $existingPet = Pet::factory(['name' => 'doggie'])
            ->for(Category::factory())
            ->has(Photo::factory()->count(2))
            ->has(Tag::factory()->count(3))
            ->create();

        $response = $this->call('delete', "/pet/{$existingPet->id}");

        $this->assertEquals(200, $response->status());
        $this->notSeeInDatabase('pets', ['name' => 'doggie']);
        $this->assertEquals(1, Category::count());
        $this->notSeeInDatabase('photos', []);
        $this->assertEquals(3, Tag::count());
        $this->notSeeInDatabase('pet_tag', []);
    }
}
