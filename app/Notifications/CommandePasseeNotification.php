<?php
namespace App\Notifications;

use App\Models\Commande;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CommandePasseeNotification extends Notification
{
    public function __construct(public Commande $commande) {}

    public function via($notifiable): array
    {
        return ['database', 'mail']; // on ajoutera 'vonage' pour SMS
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('AfriTex — Votre commande #'.$this->commande->id.' est confirmée !')
            ->greeting('Bonjour '.$notifiable->name.' !')
            ->line('Nous avons bien reçu votre commande.')
            ->line('Montant total : **'.$this->commande->total.' FCFA**')
            ->action('Voir ma commande', url('/commandes/'.$this->commande->id))
            ->line('Merci pour votre confiance — AfriTex 🎉');
    }

    public function toArray($notifiable): array
    {
        return [
            'message'     => 'Votre commande #'.$this->commande->id.' a été passée.',
            'commande_id' => $this->commande->id,
            'total'       => $this->commande->total,
            'type'        => 'commande_passee',
        ];
    }
}