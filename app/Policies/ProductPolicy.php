<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admin and editors can view all products
        return $user->hasRole('admin') || $user->hasRole('editor');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        // Admin and editors can view any product
        if ($user->hasRole('admin') || $user->hasRole('editor')) {
            return true;
        }

        // Active products can be viewed by anyone
        return $product->is_active;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admin and editors can create products
        return $user->hasRole('admin') || $user->hasRole('editor');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        // Only admin and editors can update products
        return $user->hasRole('admin') || $user->hasRole('editor');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        // Only admin and editors can delete products
        return $user->hasRole('admin') || $user->hasRole('editor');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        // Only admin and editors can restore products
        return $user->hasRole('admin') || $user->hasRole('editor');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        // Only admin can permanently delete products
        return $user->hasRole('admin');
    }
}
