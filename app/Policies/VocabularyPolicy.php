<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vocabulary;
use Illuminate\Auth\Access\Response;

class VocabularyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Any authenticated user can view their vocabularies
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Vocabulary $vocabulary): bool
    {
        return $user->id === $vocabulary->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create vocabularies
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Vocabulary $vocabulary): bool
    {
        return $user->id === $vocabulary->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Vocabulary $vocabulary): bool
    {
        return $user->id === $vocabulary->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Vocabulary $vocabulary): bool
    {
        return $user->id === $vocabulary->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Vocabulary $vocabulary): bool
    {
        return $user->id === $vocabulary->user_id;
    }
}
