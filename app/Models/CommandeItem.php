<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommandeItem extends Model
{
    protected $fillable = [
        'commande_id',
        'produit_id',
        'type_commande',
        'couleur',
        'quantite',
        'prix_unitaire',
        'sous_total',
    ];

    // ✅ Relation manquante — c'est ça qui causait l'erreur
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    /** Libellés des formats de vente : ceux du détail (manches courtes ou longues) et ceux de l'Espace Pro. */
    public const LIBELLES_FORMATS = [
        'metre' => 'Au mètre',
        'rouleau' => 'Au rouleau',
        'pantalon' => 'Pantalon',
        'chemise' => 'Chemise',
        'tunique' => 'Tunique',
        'chemise_courte' => 'Chemise manches courtes',
        'chemise_longue' => 'Chemise manches longues',
        'tunique_courte' => 'Tunique manches courtes',
        'tunique_longue' => 'Tunique manches longues',
    ];

    public function libelleFormat(): string
    {
        return self::LIBELLES_FORMATS[$this->type_commande] ?? ucfirst(str_replace('_', ' ', (string) $this->type_commande));
    }

    /** Quantité à afficher : la colonne est décimale (demi-mètre), 25.00 -> « 25 », 1.50 -> « 1,5 ». */
    public function quantiteLisible(): string
    {
        return rtrim(rtrim(number_format((float) $this->quantite, 2, ',', ''), '0'), ',');
    }
}