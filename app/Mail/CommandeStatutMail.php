<?php

namespace App\Mail;

use App\Models\Commande;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommandeStatutMail extends Mailable
{
    use Queueable, SerializesModels;

    public Commande $commande;
    public string $ancienStatut;

    public function __construct(Commande $commande, string $ancienStatut)
    {
        $this->commande    = $commande;
        $this->ancienStatut = $ancienStatut;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📦 Votre commande AfriTex #' . str_pad($this->commande->id, 4, '0', STR_PAD_LEFT) . ' — Statut mis à jour',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.commande-statut',
        );
    }
}