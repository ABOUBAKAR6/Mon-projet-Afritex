<?php

namespace App\Notifications;

use App\Models\Paiement;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaiementConfirmeNotification extends Notification
{
    public function __construct(public Paiement $paiement) {}

    public function via($notifiable): array
    {
        // Notification dans le compte uniquement : l'e-mail est CommandeRecueMail (Commande::envoyerConfirmation)
        return ['database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $isPending = $this->paiement->statut === 'en_attente';
        $subject = $isPending ? boutique('nom').' — Commande enregistrée 📦' : boutique('nom').' — Paiement confirmé ✅';
        $message = $isPending
            ? 'Votre commande a été enregistrée. Le paiement se fera à la livraison.'
            : 'Votre paiement a été confirmé avec succès.';

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Bonjour '.$notifiable->name.' !')
            ->line($message)
            ->line('Montant : **'.$this->paiement->montant.' FCFA**')
            ->action('Voir la commande', url('/commandes/'.$this->paiement->commande_id))
            ->line('Merci pour votre confiance.');
    }

    public function toArray($notifiable): array
    {
        return [
            'message'     => 'Paiement confirmé pour la commande #'.$this->paiement->commande_id,
            'paiement_id' => $this->paiement->id,
            'montant'     => $this->paiement->montant,
            'type'        => 'paiement_confirme',
        ];
    }
}