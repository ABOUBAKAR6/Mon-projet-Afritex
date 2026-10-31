<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Équipe (réservé au propriétaire) : comptes secrétaires et droits accordés à chacun.
 * Un secrétaire se connecte par /espace-client et ne voit que les sections cochées.
 */
class EquipeController extends Controller
{
    public function index(): View
    {
        return view('admin.equipe', [
            'secretaires' => User::where('role', 'secretaire')->with('permissions')->orderBy('name')->get(),
            'droits' => User::DROITS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $existant = User::where('email', mb_strtolower((string) $request->email))->first();

        $donnees = $request->validateWithBag('creation', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            // Mot de passe exigé pour un nouveau compte ; facultatif si l'adresse appartient déjà à un client
            'password' => [$existant ? 'nullable' : 'required', Password::defaults()],
            'droits' => ['array'],
            'droits.*' => [Rule::in(array_keys(User::DROITS))],
        ], [
            'password.required' => 'Choisissez un mot de passe de départ : la personne pourra le changer ensuite.',
        ]);

        if ($existant && $existant->role !== 'client') {
            return back()->withInput()->withErrors(['email' => 'Cette adresse appartient déjà à un membre de l\'équipe.'], 'creation');
        }

        // Un client existant (ou un ancien secrétaire) devient secrétaire : son compte et ses commandes sont conservés
        $secretaire = $existant ?? new User();
        $secretaire->fill([
            'name' => $donnees['name'],
            'email' => $donnees['email'],
            'phone' => $donnees['phone'] ?? $secretaire->phone,
        ]);
        $secretaire->role = 'secretaire';
        if (!empty($donnees['password'])) {
            $secretaire->password = Hash::make($donnees['password']);
        }
        $secretaire->save();
        $secretaire->syncPermissions($donnees['droits'] ?? []);

        return redirect()->route('admin.equipe')->with('success', $existant
            ? $secretaire->name . ' a rejoint l\'équipe avec son compte existant.'
            : 'Le compte de ' . $secretaire->name . ' est créé. Il se connecte sur /espace-client avec son e-mail et le mot de passe choisi.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->estSecretaire(), 404);

        $donnees = $request->validate([
            'droits' => ['array'],
            'droits.*' => [Rule::in(array_keys(User::DROITS))],
        ]);

        $user->syncPermissions($donnees['droits'] ?? []);

        return back()->with('success', 'Les droits de ' . $user->name . ' sont enregistrés.');
    }

    // Retire l'accès à l'administration ; le compte redevient un compte client (rien n'est supprimé)
    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->estSecretaire(), 404);

        $user->syncPermissions([]);
        $user->update(['role' => 'client']);

        return back()->with('success', $user->name . ' n\'a plus accès à l\'espace de gestion.');
    }
}
