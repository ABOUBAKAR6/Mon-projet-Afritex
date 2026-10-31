<?php

namespace App\Console\Commands;

use App\Models\Commande;
use Illuminate\Console\Command;

/**
 * E-mails « commande bien reçue » qui ne sont pas partis (serveur mail indisponible au moment de la commande).
 *
 *   php artisan commandes:confirmations              liste les commandes des 3 derniers jours à confirmer, sans rien envoyer
 *   php artisan commandes:confirmations --envoyer    envoie à toutes celles de la liste
 *   php artisan commandes:confirmations 31 35 --forcer   renvoie à ces commandes, même déjà marquées « envoyé »
 */
class ConfirmationsCommandes extends Command
{
    protected $signature = 'commandes:confirmations {ids?* : Numéros de commande} {--envoyer : Envoyer les e-mails} {--forcer : Renvoyer même si l\'e-mail est marqué envoyé}';

    protected $description = 'Liste ou envoie les e-mails « commande bien reçue » manquants';

    public function handle(): int
    {
        $ids = $this->argument('ids');
        $forcer = (bool) $this->option('forcer');

        $requete = Commande::with(['user', 'paiement']);
        if ($ids) {
            $requete->whereIn('id', $ids);
        } else {
            // Sans numéro : les commandes réellement passées (paiement espèces ou paiement confirmé) jamais confirmées,
            // des 3 derniers jours seulement : les plus anciennes datent d'avant ces e-mails, pas question de les relancer.
            $requete->whereNull('confirmation_envoyee_at')
                ->where('created_at', '>=', now()->subDays(3))
                ->whereHas('paiement', fn ($p) => $p->where('methode', 'especes')->orWhere('statut', 'confirme'));
        }
        $commandes = $requete->orderBy('id')->get();

        if ($commandes->isEmpty()) {
            $this->info('Aucune commande à confirmer.');
            return self::SUCCESS;
        }

        $this->table(['Commande', 'Client', 'Passée le', 'E-mail marqué envoyé'], $commandes->map(fn ($c) => [
            '#' . str_pad($c->id, 4, '0', STR_PAD_LEFT),
            $c->user?->email ?? '(sans compte)',
            $c->created_at?->format('d/m/Y H:i'),
            $c->confirmation_envoyee_at?->format('d/m/Y H:i') ?? 'non',
        ]));

        if (!$this->option('envoyer') && !$forcer) {
            $this->line('Rien n\'a été envoyé. Ajoutez --envoyer pour envoyer ces e-mails.');
            return self::SUCCESS;
        }

        foreach ($commandes as $commande) {
            if ($forcer) {
                $commande->update(['confirmation_envoyee_at' => null]);
            } elseif ($commande->confirmation_envoyee_at) {
                $this->line('ignorée  #' . str_pad($commande->id, 4, '0', STR_PAD_LEFT) . ' : déjà marquée envoyée (ajoutez --forcer pour renvoyer)');
                continue;
            }
            $ok = $commande->envoyerConfirmation(differe: false);
            $this->line(($ok ? '<info>envoyé</info>   ' : '<error>échec</error>    ') . '#' . str_pad($commande->id, 4, '0', STR_PAD_LEFT) . ' → ' . ($commande->user?->email ?? '-'));
        }

        return self::SUCCESS;
    }
}
