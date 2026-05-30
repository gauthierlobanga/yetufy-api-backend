<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantDatabaseNotificationsSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $userId,
        public readonly string $tenantId,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel("tenant.{$this->tenantId}.users.{$this->userId}");
    }

    public function broadcastAs(): string
    {
        return 'database-notifications.sent';
    }
}

