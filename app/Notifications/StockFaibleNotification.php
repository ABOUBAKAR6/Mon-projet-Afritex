<?php

namespace App\Notifications;

use App\Models\Produit;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class StockFaibleNotification extends Notification
{
    public function __construct(public Produit $produit) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('AfriTex — Stock faible ⚠️')
            ->greeting('Bonjour '.$notifiable->name)
            ->line('Le stock du produit suivant est faible :')
            ->line('Produit : **'.$this->produit->nom.'**')
            ->line('Stock restant : **'.$this->produit->stock.'**')
            ->action('Gérer le stock', url('/admin/produits/'.$this->produit->id))
            ->line('Action recommandée rapidement.');
    }

    public function toArray($notifiable): array
    {
        return [
            'message'     => 'Stock faible pour le produit '.$this->produit->nom,
            'produit_id'  => $this->produit->id,
            'stock'       => $this->produit->stock,
            'type'        => 'stock_faible',
        ];
    }
}