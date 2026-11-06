<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Messages de l'Espace Pro, dans les deux sens :
 * vers l'équipe (nouvelle demande de compte, nouveau devis demandé, devis accepté ou refusé)
 * et vers le revendeur (compte validé ou refusé, devis prêt).
 * Un sujet, quelques lignes, un bouton : e-mail et notification dans le compte.
 */
class EspaceProNotification extends Notification
{
    /**
     * Types envoyés à l'équipe de la boutique : l'e-mail finit par une ligne neutre
     * au lieu de la signature « L'équipe … », réservée aux revendeurs.
     */
    private const TYPES_EQUIPE = ['demande_pro', 'devis_demande', 'devis_modification', 'devis_accepte', 'devis_refuse'];

    /** @param  array<int, string>  $lignes */
    public function __construct(
        public string $sujet,
        public array $lignes,
        public string $lien,
        public string $bouton,
        public string $type = 'espace_pro',
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * E-mail aux couleurs de la boutique (mêmes en-tête, couleurs et pied que l'e-mail de bienvenue),
     * en HTML et en texte brut.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(boutique('nom').' · '.$this->sujet)
            ->view(['emails.espace-pro', 'emails.espace-pro-texte'], [
                'nom' => $notifiable->name,
                'sujet' => $this->sujet,
                'lignes' => $this->lignes,
                'lien' => $this->lien,
                'bouton' => $this->bouton,
                'pourEquipe' => in_array($this->type, self::TYPES_EQUIPE, true),
            ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => $this->sujet,
            'lien' => $this->lien,
            'type' => $this->type,
        ];
    }
}
