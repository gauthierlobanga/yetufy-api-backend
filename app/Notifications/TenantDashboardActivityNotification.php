<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TenantDashboardActivityNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        private readonly array $payload
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->filamentPayload();
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return (new BroadcastMessage($this->filamentPayload()))->onConnection('sync');
    }

    public function tenantId(): ?string
    {
        $tenantId = $this->payload['tenant_id'] ?? null;

        return $tenantId !== null ? (string) $tenantId : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            ...$this->filamentPayload(),
            'id' => $this->id,
            'notification_type' => self::class,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function filamentPayload(): array
    {
        $message = (string) ($this->payload['message'] ?? '');
        $type = (string) ($this->payload['type'] ?? 'system');
        $action = (string) ($this->payload['action'] ?? '');

        return [
            ...$this->payload,
            'format' => 'filament',
            'title' => (string) ($this->payload['title'] ?? 'Notification'),
            'body' => $message,
            'message' => $message,
            'status' => $this->filamentStatus($type, $action),
            'icon' => $this->filamentIcon($type),
            'iconColor' => $this->filamentStatus($type, $action),
            'duration' => 'persistent',
            'actions' => [],
            'view' => null,
            'viewData' => [],
        ];
    }

    private function filamentStatus(string $type, string $action): string
    {
        if ($action === 'deleted') {
            return 'warning';
        }

        return match ($type) {
            'payment' => 'success',
            'cart', 'order' => 'info',
            default => 'primary',
        };
    }

    private function filamentIcon(string $type): string
    {
        return match ($type) {
            'cart' => 'heroicon-o-shopping-cart',
            'order' => 'heroicon-o-shopping-bag',
            'payment' => 'heroicon-o-banknotes',
            default => 'heroicon-o-bell',
        };
    }
}
