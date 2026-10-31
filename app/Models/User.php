<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'photo', 'phone', 'referral_code', 'referred_by', 'loyalty_points', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Droits que le propriétaire peut accorder à un secrétaire, dans l'ordre du menu de l'administration.
     * Clé = permission Spatie ; route = page ouverte par ce droit.
     * Réservé au propriétaire (role « admin »), sans case à cocher : l'équipe, les rôles, le logo.
     */
    public const DROITS = [
        'view-dashboard' => ['libelle' => 'Tableau de bord', 'aide' => 'Statistiques et chiffre d\'affaires', 'route' => 'admin.dashboard'],
        'manage-orders' => ['libelle' => 'Commandes', 'aide' => 'Voir les commandes et changer leur statut', 'route' => 'admin.commandes'],
        'manage-products' => ['libelle' => 'Produits', 'aide' => 'Ajouter, modifier, supprimer, photos et stock', 'route' => 'admin.produits'],
        'manage-categories' => ['libelle' => 'Catégories', 'aide' => 'Créer et modifier les familles de tissus', 'route' => 'admin.categories'],
        'manage-users' => ['libelle' => 'Clients', 'aide' => 'Voir la liste des comptes', 'route' => 'admin.users'],
    ];

    // Le propriétaire de la boutique : tous les droits, y compris l'équipe
    public function estProprietaire(): bool
    {
        return $this->role === 'admin';
    }

    public function estSecretaire(): bool
    {
        return $this->role === 'secretaire';
    }

    // Peut entrer dans l'espace de gestion (ce qu'il y voit dépend ensuite de ses droits)
    public function estPersonnel(): bool
    {
        return $this->estProprietaire() || $this->estSecretaire();
    }

    // Page d'arrivée dans l'administration : la première section autorisée, sinon « Mon compte »
    public function premiereSectionAdmin(): string
    {
        foreach (self::DROITS as $droit => $infos) {
            if ($this->can($droit)) {
                return $infos['route'];
            }
        }

        return 'admin.compte';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class);
    }

    public function alertesStock()
    {
        return $this->hasMany(AlerteStock::class);
    }
}
