<?php

namespace App\Notifications;

use App\Models\Wishlist;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class WishlistReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public Wishlist $wishlist) {}

    public function via($notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Rappel wishlist',
            'message' => 'Vous avez des articles dans votre wishlist. Consultez-les et ajoutez-les à votre panier !',
            'url' => route('tenant.wishlist.index'),
            'type' => 'wishlist_reminder',
            'wishlist_id' => $this->wishlist->id,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
