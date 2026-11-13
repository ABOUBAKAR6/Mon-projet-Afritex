<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Produit extends Model
{
    use Searchable;

    protected $fillable = [
        'categorie_id',
        'nom',
        'slug',
        'description',
        'prix_metre',
        'prix_rouleau',
        'prix_pantalon',
        'prix_chemise',
        'prix_tunique',
        'tarifications',
        'image',
        'couleur_image',
        'stock',
        'metres_par_rouleau',
        'metres_entames',
        'disponible',
        'couleurs',
        'prix_pro',
    ];

    /**
     * Les 5 formats de vente : clé (type_commande), libellé, colonne du prix détail, unité.
     * Le prix pro de chaque format est dans la colonne JSON « prix_pro », sous la même clé.
     */
    public const FORMATS = [
        'metre' => ['libelle' => 'Au mètre', 'colonne' => 'prix_metre', 'unite' => 'le mètre'],
        'rouleau' => ['libelle' => 'Au rouleau', 'colonne' => 'prix_rouleau', 'unite' => 'le rouleau'],
        // Confections (pantalon, chemise, tunique) retirées de la vente le 2026-11-07 à la demande de la boutique :
        // leurs colonnes de prix restent en base, et les anciennes commandes gardent leur libellé (CommandeItem).
    ];

    protected $casts = [
        'couleurs' => 'array',
        'tarifications' => 'array',
        'prix_pro' => 'array',
        'stock' => 'integer',
        'metres_par_rouleau' => 'float',
        'metres_entames' => 'float',
    ];

    /** Sous ce nombre de rouleaux entiers, le tissu est « à surveiller ». */
    public const SEUIL_ROULEAUX = 5;

    /*
     | Stock : « stock » = rouleaux entiers, « metres_entames » = mètres du rouleau ouvert,
     | « metres_par_rouleau » = longueur d'un rouleau (null tant qu'elle n'est pas connue).
     | Les mouvements passent par App\Services\GestionStock, qui tient l'historique.
     */

    /** Mètres en tout (null si la longueur d'un rouleau n'est pas connue). */
    public function metresTotal(): ?float
    {
        return $this->metres_par_rouleau
            ? round($this->stock * $this->metres_par_rouleau + $this->metres_entames, 2)
            : null;
    }

    /** « 24 rouleaux + 12 m », « 1 rouleau », « 8 m », « Épuisé ». */
    public function libelleStock(): string
    {
        $morceaux = [];
        if ($this->stock > 0) {
            $morceaux[] = $this->stock.' rouleau'.($this->stock > 1 ? 'x' : '');
        }
        if ($this->metres_entames > 0) {
            $morceaux[] = self::metres($this->metres_entames);
        }

        return $morceaux ? implode(' + ', $morceaux) : 'Épuisé';
    }

    /** epuise, faible ou ok. */
    public function etatStock(): string
    {
        if ($this->stock <= 0 && $this->metres_entames <= 0) {
            return 'epuise';
        }

        return $this->stock < self::SEUIL_ROULEAUX ? 'faible' : 'ok';
    }

    /** 12.5 -> « 12,5 m » ; 30 -> « 30 m ». */
    public static function metres(float $metres): string
    {
        // Espace insécable : « 17,5 m » ne se coupe pas en fin de ligne
        return rtrim(rtrim(number_format($metres, 2, ',', ' '), '0'), ',')."\u{00A0}m";
    }

    public function mouvementsStock()
    {
        return $this->hasMany(MouvementStock::class);
    }

    /** Prix détail d'un format (null s'il n'est pas vendu dans ce format). */
    public function prixDetail(string $format): ?int
    {
        $colonne = self::FORMATS[$format]['colonne'] ?? null;
        $prix = $colonne ? (int) $this->{$colonne} : 0;

        return $prix > 0 ? $prix : null;
    }

    /** Prix pro d'un format (null si la boutique n'en a pas fixé). */
    public function prixPro(string $format): ?int
    {
        $prix = (int) ($this->prix_pro[$format] ?? 0);

        return $prix > 0 ? $prix : null;
    }

    /** Prix appliqué dans l'Espace Pro : le prix pro s'il est fixé, sinon le prix public. */
    public function prixPourPro(string $format): ?int
    {
        return $this->prixPro($format) ?? $this->prixDetail($format);
    }

    /**
     * Formats proposés dans l'Espace Pro : tous ceux qui ont un prix (pro, ou public à défaut).
     * [clé => [libelle, unite, pro, detail, tarif_pro]] ; tarif_pro = false quand c'est le prix public.
     */
    public function formatsPro(): array
    {
        $formats = [];
        foreach (self::FORMATS as $cle => $format) {
            if ($prix = $this->prixPourPro($cle)) {
                $formats[$cle] = $format + ['pro' => $prix, 'detail' => $this->prixDetail($cle), 'tarif_pro' => (bool) $this->prixPro($cle)];
            }
        }

        return $formats;
    }

    public function toSearchableArray()
    {
        return [
            'id' => (int) $this->id,
            'nom' => $this->nom,
            'description' => $this->description,
            'categorie' => $this->categorie ? $this->categorie->nom : null,
            'couleurs' => $this->couleurs,
        ];
    }

    public function setNomAttribute($value)
    {
        $this->attributes['nom'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    // Relation avec Categorie
    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }

    // Relation avec CommandeItem
    public function commandeItems()
    {
        return $this->hasMany(CommandeItem::class);
    }

    // Relation avec ProduitImage
    public function images()
    {
        // Photos supplémentaires dans l'ordre choisi (puis d'envoi) : la page produit les fait glisser dans cet ordre
        return $this->hasMany(ProduitImage::class)->orderBy('ordre')->orderBy('id');
    }

    // Relation with Review
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function averageRating()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }
}
