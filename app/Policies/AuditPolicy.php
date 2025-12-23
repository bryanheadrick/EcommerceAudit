<?php

namespace App\Policies;

use App\Models\Audit;
use App\Models\User;

class AuditPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Audit $audit): bool
    {
        return $user->id === $audit->created_by;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Audit $audit): bool
    {
        return $user->id === $audit->created_by;
    }

    public function delete(User $user, Audit $audit): bool
    {
        return $user->id === $audit->created_by;
    }

    public function restore(User $user, Audit $audit): bool
    {
        return $user->id === $audit->created_by;
    }

    public function forceDelete(User $user, Audit $audit): bool
    {
        return $user->id === $audit->created_by;
    }
}
