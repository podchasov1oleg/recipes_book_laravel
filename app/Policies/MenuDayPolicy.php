<?php

namespace App\Policies;

use App\Models\MenuDay;
use App\Models\User;

/**
 *
 */
class MenuDayPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MenuDay $menuDay): bool
    {
        return $menuDay->household_id === $user->household->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MenuDay $menuDay): bool
    {
        return $menuDay->household_id === $user->household->id;
    }
}
