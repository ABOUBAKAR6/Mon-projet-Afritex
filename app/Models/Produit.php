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
        'stock',
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
        'pantalon' => ['libelle' => 'Pantalon', 'colonne' => 'prix_pantalon', 'unite' => 'la pièce'],
        'chemise' => ['libelle' => 'Chemise', 'colonne' => 'prix_chemise', 'unite' => 'la pièce'],
        'tunique' => ['libelle' => 'Tunique', 'colonne' => 'prix_tunique', 'unite' => 'la pièce'],
    ];

    protected $casts = [
        'couleurs' => 'array',
        'tarifications' => 'array',
        'prix_pro' => 'array',
    ];

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

    /**
     * Formats proposés aux pros : ceux qui ont un prix pro.
     * [clé => [libelle, unite, pro, detail]]
     */
    public function formatsPro(): array
    {
        $formats = [];
        foreach (self::FORMATS as $cle => $format) {
            if ($pro = $this->prixPro($cle)) {
                $formats[$cle] = $format + ['pro' => $pro, 'detail' => $this->prixDetail($cle)];
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
        return $this->hasMany(ProduitImage::class);
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
