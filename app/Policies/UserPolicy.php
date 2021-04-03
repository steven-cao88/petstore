<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function before(User $user, $ability)
    {
        if ($user->isAdministrator()) {
            return true;
        }
    }

    public function view(User $user, User $account)
    {
        return $user->id === $account->id;
    }

    public function create(User $user)
    {
        return $user->isAdministrator();
    }

    public function update(User $user, User $account)
    {
        return $user->isAdministrator() || $user->id === $account->id;
    }

    public function delete(User $user)
    {
        return $user->isAdministrator();
    }
}
