<?php

namespace App\Http\Controllers;

use App\Enums\PetStatus;
use App\Models\Category;
use App\Models\Pet;
use App\Models\Photo;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PetController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(string $petId)
    {
        Gate::authorize('view', Pet::class);

        $pet = Pet::findOrFail($petId);

        return $this->format($pet);
    }

    public function findByStatus(Request $request)
    {
        $this->validate($request, [
            'status' => 'required|array'
        ]);

        $pets = Pet::whereIn('status', $request->status)->get();

        return $pets->map(fn ($pet) => $this->format($pet));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Pet::class);

        $this->validate($request, [
            'category' => 'required|string|exists:categories,name',
            'name' => 'required|string|unique:pets',
            'photoUrls' => 'required|array',
            'tags' => 'required|array',
            'status' => [
                'required',
                Rule::in(PetStatus::getOptions())
            ]
        ]);

        $pet = Pet::create([
            'name' => $request->input('name'),
            'status' => $request->input('status', PetStatus::AVAILABLE)
        ]);

        if ($request->filled('category')) {
            $category = Category::where('name', $request->input('category'))->first();

            if (!empty($category)) {
                $pet->category()->associate($category);
            }
        }

        if ($request->filled('photoUrls')) {
            $pet->photos()->createMany(
                collect($request->input('photoUrls'))
                    ->map(fn ($photoUrl) => ['url' => $photoUrl])
            );
        }

        if ($request->filled('tags')) {
            $tagIds = [];

            foreach ($request->input('tags') as $tagName) {
                $tag = Tag::where('name', $tagName)->first();

                if (!empty($tag)) {
                    $tagIds[] = $tag->id;
                }
            }

            $pet->tags()->sync($tagIds);
        }

        $pet->save();

        return 'successful operation';
    }

    public function update(Request $request)
    {
        Gate::authorize('update', Pet::class);

        $this->validate($request, [
            'id' => 'required|integer|min:1',
            'category' => 'required|string|exists:categories,name',
            'name' => [
                'required',
                'string',
                Rule::unique('pets', 'name')->ignore($request->input('id'))
            ],
            'photoUrls' => 'required|array',
            'tags' => 'required|array',
            'status' => [
                'required',
                Rule::in(PetStatus::getOptions())
            ]
        ]);

        $pet = Pet::findOrFail($request->input('id'));

        $properties = ['name', 'status'];

        foreach ($properties as $property) {
            if ($request->filled($property)) {
                $pet->$property = $request->input($property);
            }
        }

        if ($request->filled('category')) {
            $category = Category::where('name', $request->input('category'))->first();

            if (!empty($category)) {
                $pet->category()->associate($category);
            }
        }

        if ($request->filled('photoUrls')) {
            Photo::upsert(
                collect($request->input('photoUrls'))
                    ->map(fn ($photoUrl) => [
                        'pet_id' => $pet->id,
                        'url' => $photoUrl
                    ])->toArray(),
                ['pet_id', 'url'],
                ['url']
            );
        }

        if ($request->filled('tags')) {
            $tagIds = [];

            foreach ($request->input('tags') as $tagName) {
                $tag = Tag::where('name', $tagName)->first();

                if (!empty($tag)) {
                    $tagIds[] = $tag->id;
                }
            }

            $pet->tags()->sync($tagIds);
        }

        $pet->save();

        return 'successful operation';
    }

    public function uploadImage(Request $request, int $petId)
    {
        Gate::authorize('update', Pet::class);

        $this->validate($request, [
            'petId' => 'required|integer',
            'file' => 'required|file',
            'additionalMetadata' => 'string|nullable'
        ]);

        $pet = Pet::findOrFail($petId);

        if (!$request->hasFile('file')) {
            abort(403);
        }

        if (!empty($pet) && ($request->hasFile('file')) && ($request->file('file')->isValid())) {
            $filePath = $request->file('file')->store('photos');

            $pet->photos()->create(['url' => $filePath]);

            return [
                'code' => 0,
                'type' => 'success',
                'message' => 'Photo is uploaded successfully.'
            ];
        }
    }

    protected function format(Pet $pet)
    {
        return [
            'id' => $pet->id,
            'category' => [
                'id' => $pet->category->id,
                'name' => $pet->category->name
            ],
            'name' => $pet->name,
            'photoUrls' => $pet->photos->pluck('url'),
            'tags' => $pet->tags->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $tag->name
            ]),
            'status' => $pet->status
        ];
    }

    public function destroy(int $petId)
    {
        Gate::authorize('delete', Pet::class);

        $pet = Pet::findOrFail($petId);

        $pet->photos()->delete();
        $pet->category()->disassociate();
        $pet->tags()->detach();
        $pet->delete();
    }
}
