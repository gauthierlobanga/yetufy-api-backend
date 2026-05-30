<?php

namespace App\Listeners;

use App\Events\WishlistActivity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class WishlistActivityListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(WishlistActivity $event): void
    {
        //
    }
}
