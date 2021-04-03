<?php

use App\Enums\PetStatus;
use App\Enums\Role;
use App\Models\Category;
use App\Models\Pet;
use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UpdatePetTest extends TestCase
{
    use DatabaseMigrations;

    protected $faker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();
    }

    public function test_admin_can_update_pet()
    {
        $admin = User::factory()->create(['role' => Role::STORE_ADMIN]);

        $this->actingAs($admin);

        $faker = $this->faker;

        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        $tagSet2 = Tag::factory()->count(3)->create();

        $existingPet = Pet::factory(['status' => PetStatus::AVAILABLE])
            ->for($category1)
            ->has(Photo::factory()->count(2))
            ->has(Tag::factory()->count(3))
            ->create();

        $newPhotos = [
            $faker->url,
            $faker->url
        ];

        $newPetData = [
            'id' => $existingPet->id,
            'category' => $category2->name,
            'name' => 'sparky',
            'photoUrls' => $newPhotos,
            'tags' => $tagSet2->pluck('name')->toArray(),
            'status' => PetStatus::PENDING
        ];

        $response = $this->call('put', '/pet', $newPetData);

        $this->assertEquals(200, $response->status());

        $this->seeInDatabase('pets', [
            'name' => 'sparky',
            'category_id' => $category2->id,
            'status' => PetStatus::PENDING
        ]);

        $this->assertCount(
            0,
            array_diff(
                $newPhotos,
                $existingPet->photos->pluck('url')->toArray()
            )
        );

        $this->assertCount(
            0,
            array_diff(
                $tagSet2->pluck('name')->toArray(),
                $existingPet->tags->pluck('name')->toArray()
            )
        );
    }

    public function test_normal_user_can_not_update_pet()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $faker = $this->faker;

        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        $tagSet2 = Tag::factory()->count(3)->create();

        $existingPet = Pet::factory(['status' => PetStatus::AVAILABLE])
            ->for($category1)
            ->has(Photo::factory()->count(2))
            ->has(Tag::factory()->count(3))
            ->create();

        $newPhotos = [
            $faker->url,
            $faker->url
        ];

        $newPetData = [
            'id' => $existingPet->id,
            'category' => $category2->name,
            'name' => 'sparky',
            'photoUrls' => $newPhotos,
            'tags' => $tagSet2->pluck('name')->toArray(),
            'status' => PetStatus::PENDING
        ];

        $response = $this->call('put', '/pet', $newPetData);

        $this->assertEquals(403, $response->status());

        $this->notSeeInDatabase('pets', [
            'name' => 'sparky',
            'category_id' => $category2->id,
            'status' => PetStatus::PENDING
        ]);

        $this->assertCount(
            2,
            array_diff(
                $newPhotos,
                $existingPet->photos->pluck('url')->toArray()
            )
        );

        $this->assertCount(
            3,
            array_diff(
                $tagSet2->pluck('name')->toArray(),
                $existingPet->tags->pluck('name')->toArray()
            )
        );
    }

    public function test_admin_can_upload_image_to_pet()
    {
        $admin = User::factory()->create(['role' => Role::STORE_ADMIN]);

        $this->actingAs($admin);

        $existingPet = Pet::factory(['status' => PetStatus::AVAILABLE])
            ->for(Category::factory())
            ->has(Photo::factory()->count(2))
            ->has(Tag::factory()->count(3))
            ->create();

        Storage::fake('local');

        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->call(
            'post',
            "/pet/{$existingPet->id}/uploadImage",
            [
                'petId' => $existingPet->id,
                'file' => $file,
            ],
            [],
            [
                'file' => $file
            ]
        );

        $this->assertEquals(200, $response->status());
        $this->seeJson([
            'message' => 'Photo is uploaded successfully.'
        ]);
        $this->assertEquals(3, $existingPet->photos()->count());
    }
}
