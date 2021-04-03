<?php

use App\Enums\PetStatus;
use App\Enums\Role;
use App\Models\Category;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class CreatePetTest extends TestCase
{
    use DatabaseMigrations;

    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();
    }

    public function test_admin_can_create_pet()
    {
        $admin = User::factory()->create(['role' => Role::STORE_ADMIN]);

        $this->actingAs($admin);

        $faker = $this->faker;

        $category = Category::factory()->create();

        $tags = Tag::factory()->count(3)->create();

        $newPetData = [
            'category' => $category->name,
            'name' => 'doggie',
            'photoUrls' => [
                $faker->url,
                $faker->url
            ],
            'tags' => $tags->map(fn ($tag) => $tag->name)->toArray(),
            'status' => PetStatus::AVAILABLE
        ];

        $response = $this->call('post', '/pet', $newPetData);

        $this->assertEquals(200, $response->status());
        $this->seeInDatabase('pets', [
            'name' => 'doggie',
            'category_id' => $category->id,
            'status' => PetStatus::AVAILABLE
        ]);
        $this->assertEquals(1, Category::count());
        $this->assertEquals(2, Photo::count());
        $this->assertEquals(3, Tag::count());
    }

    public function test_normal_user_can_not_create_pet()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $faker = $this->faker;

        $category = Category::factory()->create();

        $tags = Tag::factory()->count(3)->create();

        $newPetData = [
            'category' => $category->name,
            'name' => 'doggie',
            'photoUrls' => [
                $faker->url,
                $faker->url
            ],
            'tags' => $tags->map(fn ($tag) => $tag->name)->toArray(),
            'status' => PetStatus::AVAILABLE
        ];

        $response = $this->call('post', '/pet', $newPetData);

        $this->assertEquals(403, $response->status());
        $this->notSeeInDatabase('pets', [
            'name' => 'doggie',
            'category_id' => $category->id,
            'status' => PetStatus::AVAILABLE
        ]);
        $this->assertEquals(0, Photo::count());
    }
}
