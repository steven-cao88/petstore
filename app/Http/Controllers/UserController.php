<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
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

    public function show(string $username)
    {
        $user = User::where('username', $username)->first();

        if (empty($user)) {
            return [];
        }

        Gate::authorize('view', $user);

        return [
            'id' => $user->id,
            'username' => $user->username,
            'firstName' => $user->firstName,
            'lastName' => $user->lastName,
            'email' => $user->email,
            'password' => $user->password,
            'phone' => $user->phone,
            'userStatus' => $user->userStatus
        ];
    }

    public function store(Request $request)
    {
        Gate::authorize('create', User::class);

        $this->storeUser($request->all());

        return 'successful operation';
    }

    public function storeMultiple(Request $request)
    {
        Gate::authorize('create', User::class);

        if ($request->filled('users')) {
            foreach ($request->input('users') as $userData) {
                $this->storeUser($userData);
            }
        }

        return 'successful operation';
    }

    public function update(Request $request, string $username)
    {
        $user = User::where('username', $username)->first();

        if (empty($user)) {
            return [];
        }

        Gate::authorize('update', $user);

        $this->validate(
            $request,
            [
                'username' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('users', 'username')->ignore($user->id)
                ],
                'firstName' => 'string|nullable',
                'lastName' => 'string|nullable',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users', 'email')->ignore($user->id)
                ],
                'password' => 'required|string',
                'phone' => 'string|nullable',
                'userStatus' => 'integer|min:0|nullbable',
            ]
        );

        $requestData = $request->all();

        if (isset($requestData['password'])) {
            $requestData['password'] = Hash::make($requestData['password']);
        }

        $user->fill($requestData);

        $user->save();

        return 'successful operation';
    }

    public function destroy(string $username)
    {
        $user = User::where('username', $username)->first();

        if (empty($user)) {
            return [];
        }

        Gate::authorize('delete', User::class);

        $user->delete();

        return 'successful operation';
    }

    protected function storeUser(array $userData)
    {
        Validator::make(
            $userData,
            [
                'username' => 'required|string|unique:users|max:255',
                'firstName' => 'string|nullable',
                'lastName' => 'string|nullable',
                'email' => 'required|email|unique:users',
                'password' => 'required|string',
                'phone' => 'string|nullable',
                'userStatus' => 'integer|min:0|nullbable',
            ]
        )->validate();

        if (isset($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        }

        return User::create($userData);
    }
}
