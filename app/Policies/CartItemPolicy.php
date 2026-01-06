<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CartItem;
use Illuminate\Auth\Access\HandlesAuthorization;

class CartItemPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function destroy(User $user, CartItem $cart)
    {
        return $user->id === $cart->user_id;
    }
}
