@extends('layouts.admin')
@section('title', 'Utilisateurs · ' . boutique('nom'))
@section('content')

<x-admin-entete titre="Utilisateurs" description="Les comptes de la boutique et leur rôle." />

<section class="bg-carte" aria-label="Liste des utilisateurs">
    <div class="overflow-x-auto">
        <table class="tableau tableau-empile">
            <thead>
                <tr>
                    <th scope="col">Utilisateur</th>
                    <th scope="col">E-mail</th>
                    <th scope="col">Rôle</th>
                    <th scope="col"><span class="sr-only">Action</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    @php
                        $estAdmin = $user->estProprietaire();
                        $roleLibelle = $estAdmin ? 'Administrateur' : ($user->estSecretaire() ? 'Secrétaire' : 'Client');
                    @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span aria-hidden="true" class="titre-display flex size-10 shrink-0 items-center justify-center rounded-full {{ $estAdmin ? 'bg-brun-fonce text-or' : 'bg-brun-doux text-brun' }}">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                                <span class="font-medium">{{ $user->name }}@if($user->id === Auth::id()) <span class="font-normal text-encre-2">(vous)</span>@endif</span>
                            </div>
                        </td>
                        <td class="text-encre-2" data-label="E-mail" data-large>{{ $user->email }}</td>
                        <td data-label="Rôle" data-large><span class="pastille {{ $user->estPersonnel() ? 'pastille-brun' : '' }}">{{ $roleLibelle }}</span></td>
                        <td class="text-right">
                            {{-- Changer un rôle est réservé au propriétaire ; les secrétaires se gèrent depuis la page Équipe --}}
                            @if($user->estSecretaire())
                                @can('proprietaire')
                                    <a href="{{ route('admin.equipe') }}" class="btn btn-fantome btn-sm">Gérer ses droits</a>
                                @endcan
                            @elseif($user->id !== Auth::id() && Auth::user()->can('proprietaire'))
                                <form action="{{ route('admin.users.role', $user->id) }}" method="POST"
                                      onsubmit="return confirm(@js($estAdmin ? 'Retirer les droits d\'administration à ' . $user->name . ' ?' : 'Donner les droits d\'administration à ' . $user->name . ' ?'))">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="role" value="{{ $estAdmin ? 'client' : 'admin' }}">
                                    <button type="submit" class="btn btn-contour btn-sm">{{ $estAdmin ? 'Passer en client' : 'Passer administrateur' }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="border-t border-trait p-4">{{ $users->links() }}</div>
</section>

@endsection
