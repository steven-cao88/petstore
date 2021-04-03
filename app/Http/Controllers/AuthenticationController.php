<?php

namespace App\Http\Controllers;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthenticationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'except' => ['login']
        ]);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string|max:255',
            'password' => 'required|string'
        ]);

        $user = User::where('username', $request->input('username'))
            ->first();

        if (empty($user) && !Hash::check($request->input('password'), $user->password)) {
            abort(400, 'Invalid username/password supplied');
        }

        $token = $user->createApiToken();

        return response(['api_token' => $token->value], 200)
            ->header('X-Expires-After', $token->expired_at)
            ->header('X-Rate-Limit', 60);
    }

    public function logout(Request $request)
    {
        $this->validate($request, [
            'api_token' => 'required|string|exists:api_tokens,value'
        ]);

        $apiToken = ApiToken::where('value', $request->input('api_token'))->first();

        if ($apiToken->user_id !== $request->user()->id) {
            abort(401);
        }

        $apiToken->delete();

        return 'successful operation';
    }
}
