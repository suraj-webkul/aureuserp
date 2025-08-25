<?php

namespace Webkul\Security\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Webkul\Security\Models\User;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \Webkul\Security\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_users::user');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \Webkul\Security\Models\User  $user
     * @return bool
     */
    public function view(User $user): bool
    {
        return $user->can('view_users::user');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \Webkul\Security\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create_users::user');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \Webkul\Security\Models\User  $user
     * @return bool
     */
    public function update(User $user): bool
    {
        return $user->can('update_users::user');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \Webkul\Security\Models\User  $user
     * @return bool
     */
    public function delete(User $user): bool
    {
        return $user->can('delete_users::user');
    }

    /**
     * Determine whether the user can bulk delete.
     *
     * @param  \Webkul\Security\Models\User  $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_users::user');
    }

    /**
     * Determine whether the user can permanently delete.
     *
     * @param  \Webkul\Security\Models\User  $user
     * @return bool
     */
    public function forceDelete(User $user): bool
    {
        return $user->can('force_delete_users::user');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     *
     * @param  \Webkul\Security\Models\User  $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_users::user');
    }

    /**
     * Determine whether the user can restore.
     *
     * @param  \Webkul\Security\Models\User  $user
     * @return bool
     */
    public function restore(User $user): bool
    {
        return $user->can('restore_users::user');
    }

    /**
     * Determine whether the user can bulk restore.
     *
     * @param  \Webkul\Security\Models\User  $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_users::user');
    }

    /**
     * Determine whether the user can bulk restore.
     *
     * @param  \Webkul\Security\Models\User  $user
     * @return bool
     */
    public function replicate(User $user): bool
    {
        return $user->can('replicate_users::user');
    }

    /**
     * Determine whether the user can reorder.
     *
     * @param  \Webkul\Security\Models\User  $user
     * @return bool
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_users::user');
    }
}

