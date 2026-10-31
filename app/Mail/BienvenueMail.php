<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Envoyé à l'inscription : confirme la création du compte, rappelle l'adresse enregistrée
 * et explique comment réinitialiser le mot de passe en cas d'oubli.
 */
class BienvenueMail extends Mailable
{
    public function __construct(public User $utilisateur) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenue chez AfriTex : votre compte est créé',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bienvenue',
            text: 'emails.bienvenue-texte',
            with: [
                'lienReinitialisation' => route('password.request'),
                'lienCatalogue' => route('catalogue'),
            ],
        );
    }
}
