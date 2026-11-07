<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parametre;
use App\Services\OptimiseurImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Compte de l'administrateur connecté (photo, informations, mot de passe)
 * et logo de la boutique affiché sur les pages publiques.
 */
class CompteController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.compte', [
            'admin' => $request->user(),
            'logo' => Parametre::valeur('logo'),
        ]);
    }

    public function updateInformations(Request $request): RedirectResponse
    {
        $admin = $request->user();

        $donnees = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($admin->id)],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $admin->fill($donnees);
        if ($admin->isDirty('email')) {
            $admin->email_verified_at = null;
        }
        $admin->save();

        return back()->with('success', 'Vos informations sont enregistrées.');
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.OptimiseurImage::POIDS_MAX_KO],
        ], [
            'photo.max' => 'La photo ne doit pas dépasser 20 Mo.',
        ]);

        $admin = $request->user();
        $this->supprimerFichier($admin->photo);
        $admin->update(['photo' => $this->enregistrer($request->file('photo'), 'images/profils', OptimiseurImage::PORTRAIT)]);

        return back()->with('success', 'Votre photo de profil est à jour.');
    }

    public function destroyPhoto(Request $request): RedirectResponse
    {
        $admin = $request->user();
        $this->supprimerFichier($admin->photo);
        $admin->update(['photo' => null]);

        return back()->with('success', 'Votre photo de profil a été retirée.');
    }

    public function updateMotDePasse(Request $request): RedirectResponse
    {
        $donnees = $request->validateWithBag('motDePasse', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update(['password' => $donnees['password']]);

        return back()->with('success', 'Votre mot de passe a été modifié.');
    }

    public function updateLogo(Request $request): RedirectResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.OptimiseurImage::POIDS_MAX_KO],
        ], [
            'logo.max' => 'Le logo ne doit pas dépasser 20 Mo.',
        ]);

        $this->supprimerFichier(Parametre::valeur('logo'));
        Parametre::definir('logo', $this->enregistrer($request->file('logo'), 'images/site', OptimiseurImage::LOGO));

        return back()->with('success', 'Le logo de la boutique est à jour.');
    }

    // Thème de couleurs de toute la boutique (site public, administration, e-mails)
    public function updateCouleurs(Request $request): RedirectResponse
    {
        $themes = array_keys(config('boutique.themes', []));
        $donnees = $request->validate([
            'theme' => ['required', 'string', Rule::in(['origine', ...$themes])],
        ]);

        $theme = $donnees['theme'] === 'origine' ? null : $donnees['theme'];
        Parametre::definir('theme_couleurs', $theme);

        $nom = $theme ? config('boutique.themes.'.$theme.'.nom') : 'd\'origine';

        return back()->with('success', 'Couleurs « '.$nom.' » appliquées à toute la boutique.');
    }

    public function destroyLogo(): RedirectResponse
    {
        $this->supprimerFichier(Parametre::valeur('logo'));
        Parametre::definir('logo', null);

        return back()->with('success', 'Le logo a été retiré : la boutique affiche de nouveau le « '.boutique_initiale().' ».');
    }

    // Nom aléatoire (jamais celui d'origine), image redressée, réduite et compressée en WebP
    private function enregistrer(UploadedFile $fichier, string $dossier, int $cote): string
    {
        return app(OptimiseurImage::class)->enregistrer($fichier, $dossier, $cote);
    }

    private function supprimerFichier(?string $chemin): void
    {
        if ($chemin && str_starts_with($chemin, 'images/') && is_file(public_path($chemin))) {
            unlink(public_path($chemin));
        }
    }
}
