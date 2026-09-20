<?php

namespace App\Notifications;

use App\Models\Commande;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class StatutModifieNotification extends Notification
{
    public function __construct(public Commande $commande) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $statuts = [
            'en_attente' => '⏳ En attente',
            'confirmee'  => '✅ Confirmée',
            'livree'     => '🚚 Livrée',
            'annulee'    => '❌ Annulée',
        ];

        $statut = $statuts[$this->commande->statut] ?? $this->commande->statut;

        return (new MailMessage)
            ->subject('AfriTex — Mise à jour de votre commande #' . str_pad($this->commande->id, 4, '0', STR_PAD_LEFT))
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Le statut de votre commande a été mis à jour.')
            ->line('**Commande #' . str_pad($this->commande->id, 4, '0', STR_PAD_LEFT) . '**')
            ->line('**Nouveau statut : ' . $statut . '**')
            ->line('**Montant : ' . number_format($this->commande->total, 0, ',', ' ') . ' FCFA**')
            ->action('Voir ma commande', url('/'))
            ->line('Merci de votre confiance chez AfriTex ! 🧵');
    }
}