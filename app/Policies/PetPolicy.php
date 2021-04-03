<?php

namespace App\Policies;

use App\Models\Pet;
use App\Models\User;

class PetPolicy
{
    public function before(User $user, $ability)
    {
        if ($user->isAdministrator()) {
            return true;
        }
    }

    public function viewAny(User $user)
    {
        return $user->isAdministrator();
    }

    public function view(User $user)
    {
        return $user->isAdministrator();
    }

    public function create(User $user)
    {
        return $user->isAdministrator();
    }

    public function update(User $user)
    {
        return $user->isAdministrator();
    }

    public function delete(User $user)
    {
        return $user->isAdministrator();
    }
}
