<?php

namespace App\Notifications;

use App\Models\Client;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NouveauClientNotification extends Notification
{
    public function __construct(public Client $client) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(boutique('nom').' — Nouveau client inscrit 🎉')
            ->greeting('Bonjour '.$notifiable->name)
            ->line('Un nouveau client vient de s’inscrire.')
            ->line('Nom : **'.$this->client->name.'**')
            ->line('Email : '.$this->client->email)
            ->action('Voir le client', url('/admin/clients/'.$this->client->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'message'   => 'Nouveau client inscrit : '.$this->client->name,
            'client_id' => $this->client->id,
            'email'     => $this->client->email,
            'type'      => 'nouveau_client',
        ];
    }
}