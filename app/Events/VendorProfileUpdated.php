<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class VendorProfileUpdated
{
    use Dispatchable;

    public User $user;

    public array $changes;

    public function __construct(User $user, array $changes)
    {
        $this->user = $user;
        $this->changes = $changes;
    }
}
