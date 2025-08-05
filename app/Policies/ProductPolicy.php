<?php

namespace App\Policies;

use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        $user->loadMissing('roles');
        return $user->roles->contains('name', 'super_admin') || $user->roles->contains('name', 'admin') || $user->roles->contains('name', 'staff');
    }
    public function view(User $user): bool { return $this->viewAny($user); }
    public function create(User $user): bool { return $this->viewAny($user); }
    public function update(User $user): bool { return $this->viewAny($user); }
    public function delete(User $user): bool { return $this->viewAny($user); }
}
