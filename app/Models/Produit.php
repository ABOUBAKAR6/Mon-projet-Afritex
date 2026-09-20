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
    ];

    protected $casts = [
        'couleurs' => 'array',
        'tarifications' => 'array',
    ];

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
