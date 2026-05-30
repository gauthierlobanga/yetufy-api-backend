<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WishlistActivity implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $tenantId;

    public string $title;

    public string $message;

    public string $type; // 'wishlist_add' ou 'wishlist_remove'

    public function __construct(string $tenantId, string $title, string $message, string $type = 'wishlist_add')
    {
        $this->tenantId = $tenantId;
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('tenant.'.$this->tenantId);
    }

    public function broadcastAs(): string
    {
        return 'wishlist.activity';
    }
}
