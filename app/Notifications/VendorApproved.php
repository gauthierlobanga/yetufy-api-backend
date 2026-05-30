<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorApproved extends Notification
{
    use Queueable;

    public function __construct(public Tenant $tenant) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $shopUrl = 'http://'.$this->tenant->slug.'.'.config('app.domain').'/vendeur';

        return (new MailMessage)
            ->subject('Votre boutique est prête !')
            ->greeting('Félicitations '.$notifiable->name.' !')
            ->line('Votre boutique "'.$this->tenant->raison_sociale.'" a été créée avec succès.')
            ->line('Vous pouvez maintenant commencer à configurer votre boutique et ajouter vos produits.')
            ->action('Accéder à ma boutique', $shopUrl)
            ->line('Merci d\'avoir choisi notre plateforme !');
    }

    public function toArray($notifiable): array
    {
        return [
            'tenant_id' => $this->tenant->id,
            'shop_name' => $this->tenant->raison_sociale,
            'message' => 'Votre boutique a été créée avec succès.',
        ];
    }
}
