<?php

namespace App\Models;

use App\Enums\Role;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Support\Str;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'firstName',
        'lastName',
        'email',
        'password',
        'phone',
        'userStatus',
        'role'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    // Relations
    public function apiTokens()
    {
        return $this->hasMany(ApiToken::class);
    }

    // Helpers
    public function isAdministrator()
    {
        return $this->role === Role::STORE_ADMIN;
    }

    public function createApiToken()
    {
        return $this->apiTokens()->create(
            [
                'value' => Str::random(40),
                'expired_at' => Carbon::now()->addHours(24)
            ]
        );
    }
}
