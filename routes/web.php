<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\AlerteStockController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CreationController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MagicAssistantController;
use App\Http\Controllers\YardageController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\EspaceGestionController;
use App\Http\Controllers\Admin\CompteController;
use App\Http\Controllers\Admin\EquipeController;

// ===== PAGES PUBLIQUES =====
Route::get('/', [ProduitController::class, 'index'])->name('accueil');
Route::get('/catalogue', [ProduitController::class, 'catalogue'])->name('catalogue');
Route::get('/produit/{slug}', [ProduitController::class, 'show'])->name('produit.show');
Route::get('/track/{tracking_number}', [CommandeController::class, 'track'])->name('track');
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
Route::get('/privacy', [LegalController::class, 'rgpd'])->name('privacy');
Route::get('/cgu', [LegalController::class, 'cgu'])->name('cgu');
Route::get('/a-propos', [PageController::class, 'about'])->name('about');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::post('/stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handle'])->name('stripe.webhook');

// ===== PAGES CLIENT =====
Route::get('/paiement/{commande}/callback', [PaiementController::class, 'callback'])->name('paiement.callback');

Route::middleware('auth')->group(function () {
    // Après une connexion sur la boutique, tout le monde reste côté boutique :
    // l'administration ne s'ouvre que depuis /espace-client.
    Route::get('/dashboard', function () {
        return redirect()->route('panier');
    })->name('dashboard');

    Route::get('/preferences', function () { return view('preferences'); })->name('preferences');

    // ===== ALERTES DE STOCK =====
    Route::get('/alertes-stock', [AlerteStockController::class, 'index'])->name('alertes.index');
    Route::post('/alertes-stock', [AlerteStockController::class, 'creer'])->name('alertes.creer');
    Route::delete('/alertes-stock/{alerte}', [AlerteStockController::class, 'supprimer'])->name('alertes.supprimer');
    Route::post('/alertes-stock/{alerte}/basculer', [AlerteStockController::class, 'basculer'])->name('alertes.basculer');

    Route::get('/panier', [CommandeController::class, 'panier'])->name('panier');
    Route::post('/panier/ajouter', [CommandeController::class, 'ajouter'])->name('panier.ajouter');
    Route::post('/panier/coupon', [CommandeController::class, 'appliquerCoupon'])->name('panier.coupon.appliquer');
    Route::delete('/panier/supprimer/{id}', [CommandeController::class, 'supprimer'])->name('panier.supprimer');
    Route::get('/commande', [CommandeController::class, 'index'])->name('commande');
    Route::post('/commande/confirmer', [CommandeController::class, 'confirmer'])->name('commande.confirmer');
    Route::get('/commandes', [CommandeController::class, 'mesCommandes'])->name('commandes.index');
    Route::get('/commandes/{commande}', [CommandeController::class, 'show'])->name('commandes.show');
    Route::get('/commandes/{commande}/facture', [InvoiceController::class, 'download'])->name('commandes.invoice');
    Route::get('/paiement/{commande}', [PaiementController::class, 'index'])->name('paiement');
    Route::post('/paiement/{commande}', [PaiementController::class, 'payer'])->name('paiement.payer');
    Route::post('/produit/{id}/review', [ProduitController::class, 'storeReview'])->name('produit.review.store');
    Route::get('/notifications/{id}/marquer', [NotificationController::class, 'marquer'])->name('notifications.marquer');
    Route::get('/notifications/tout-lire', [NotificationController::class, 'toutLire'])->name('notifications.tout-lire');
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::post('/wishlist/toggle/{id}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/wishlist/{id}', [WishlistController::class, 'remove'])->name('wishlist.remove');
    Route::get('/inspirations', [CreationController::class, 'index'])->name('inspirations.index');
    Route::post('/inspirations/partager', [CreationController::class, 'store'])->name('inspirations.store');
    Route::get('/magic-assistant', [MagicAssistantController::class, 'index'])->name('magic.index');
    Route::post('/magic-assistant/suggest', [MagicAssistantController::class, 'suggest'])->name('magic.suggest');
    Route::get('/calculateur', [YardageController::class, 'index'])->name('yardage.index');
    Route::post('/calculateur/calculate', [YardageController::class, 'calculate'])->name('yardage.calculate');

    // Fidélité & Parrainage
    Route::get('/fidelite', [LoyaltyController::class, 'index'])->name('loyalty.index');
    Route::post('/fidelite/code', [LoyaltyController::class, 'generateCode'])->name('loyalty.generate');
    Route::post('/fidelite/appliquer', [LoyaltyController::class, 'applyCode'])->name('loyalty.apply');

    // API: suppression de compte (auth required)
    Route::post('/api/account/delete', [AccountController::class, 'delete'])->name('api.account.delete');

    // ===== API PANIER (Cookies) =====
    Route::post('/api/panier/sync', [CommandeController::class, 'syncPanier'])->name('api.panier.sync');
    Route::get('/api/panier/current', [CommandeController::class, 'getPanierCourant'])->name('api.panier.current');
});

// ===== API PANIER (Sans authentification, accessible au guest) =====
Route::get('/api/panier/current', [CommandeController::class, 'getPanierCourant'])->name('api.panier.current.guest');

// ===== ADMIN =====
// Propriétaire et secrétaires entrent (middleware admin) ; chaque section exige ensuite son droit.
// Le propriétaire a tous les droits (Gate::before) ; « proprietaire » lui réserve l'équipe, les rôles et le logo.
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Sans le droit « tableau de bord », renvoie vers la première section autorisée
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');

    Route::middleware('can:manage-orders')->group(function () {
        Route::get('/commandes', [AdminController::class, 'commandes'])->name('admin.commandes');
        Route::get('/commandes/{commande}', [AdminController::class, 'commandeDetail'])->name('admin.commande.detail');
        Route::patch('/commandes/{commande}/statut', [AdminController::class, 'updateStatut'])->name('admin.commande.statut');
        Route::put('/commandes/{commande}/statut', [AdminController::class, 'updateStatut'])->name('admin.commandes.statut');
    });

    Route::middleware('can:manage-products')->group(function () {
        Route::get('/produits', [AdminController::class, 'produits'])->name('admin.produits');
        Route::get('/produits/ajouter', [AdminController::class, 'showAjouterProduit'])->name('admin.produits.ajouter.form');
        Route::post('/produits/ajouter', [AdminController::class, 'ajouterProduit'])->name('admin.produits.ajouter');
        Route::post('/produits/{id}/modifier', [AdminController::class, 'editProduit'])->name('admin.produits.edit');
        Route::delete('/produits/{id}', [AdminController::class, 'destroyProduit'])->name('admin.produits.destroy');
        Route::post('/produits/{produit}/images', [AdminController::class, 'ajouterImage'])->name('admin.produit.image.ajouter');
        Route::delete('/images/{image}', [AdminController::class, 'supprimerImage'])->name('admin.produit.image.supprimer');
    });

    Route::middleware('can:manage-categories')->group(function () {
        Route::get('/categories', [CategorieController::class, 'index'])->name('admin.categories');
        Route::post('/categories', [CategorieController::class, 'store'])->name('admin.categories.store');
        Route::put('/categories/{id}', [CategorieController::class, 'update'])->name('admin.categories.update');
        Route::delete('/categories/{id}', [CategorieController::class, 'destroy'])->name('admin.categories.destroy');
    });

    Route::get('/users', [AdminController::class, 'utilisateurs'])->middleware('can:manage-users')->name('admin.users');

    // Mon compte : chaque membre de l'équipe gère son propre profil
    Route::get('/compte', [CompteController::class, 'edit'])->name('admin.compte');
    Route::patch('/compte', [CompteController::class, 'updateInformations'])->name('admin.compte.informations');
    Route::post('/compte/photo', [CompteController::class, 'updatePhoto'])->name('admin.compte.photo');
    Route::delete('/compte/photo', [CompteController::class, 'destroyPhoto'])->name('admin.compte.photo.supprimer');
    Route::put('/compte/mot-de-passe', [CompteController::class, 'updateMotDePasse'])->name('admin.compte.mot-de-passe');

    // Réservé au propriétaire
    Route::middleware('can:proprietaire')->group(function () {
        Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('admin.users.role');
        Route::post('/compte/logo', [CompteController::class, 'updateLogo'])->name('admin.compte.logo');
        Route::delete('/compte/logo', [CompteController::class, 'destroyLogo'])->name('admin.compte.logo.supprimer');

        Route::get('/equipe', [EquipeController::class, 'index'])->name('admin.equipe');
        Route::post('/equipe', [EquipeController::class, 'store'])->name('admin.equipe.store');
        Route::put('/equipe/{user}', [EquipeController::class, 'update'])->name('admin.equipe.update');
        Route::delete('/equipe/{user}', [EquipeController::class, 'destroy'])->name('admin.equipe.destroy');
    });
});

// Reste des routes admin hors groupe si besoin, mais ici on a tout regroupé.
// Route::post('/produits/{id}/modifier', [AdminController::class, 'modifierProduit'])->name('admin.produits.modifier'); // C'était ici, je le déplace dans le groupe.


// ===== ESPACE DE GESTION (connexion séparée, invisible du public : clé secrète requise) =====
Route::prefix('espace-client')->middleware('cle-espace')->group(function () {
    Route::get('/', [EspaceGestionController::class, 'create'])->name('espace.connexion');
    Route::post('/', [EspaceGestionController::class, 'store'])->name('espace.connexion.envoyer');
});
Route::post('/espace-client/deconnexion', [EspaceGestionController::class, 'destroy'])
    ->middleware('auth')
    ->name('espace.deconnexion');

require __DIR__.'/auth.php';
