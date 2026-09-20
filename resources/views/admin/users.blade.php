@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestion des Utilisateurs</h1>
        <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition">Retour au Dashboard</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr class="bg-gray-100 border-b-2 border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <th class="px-5 py-3">Utilisateur</th>
                    <th class="px-5 py-3">Email</th>
                    <th class="px-5 py-3">Rôle</th>
                    <th class="px-5 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="px-5 py-5 text-sm">
                        <p class="text-gray-900 whitespace-no-wrap font-medium">{{ $user->name }}</p>
                    </td>
                    <td class="px-5 py-5 text-sm">
                        <p class="text-gray-600 whitespace-no-wrap">{{ $user->email }}</p>
                    </td>
                    <td class="px-5 py-5 text-sm">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-5 py-5 text-sm text-right">
                        <form action="{{ route('admin.users.role', $user->id) }}" method="POST" class="inline-block">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="role" value="{{ $user->role === 'admin' ? 'client' : 'admin' }}">
                            <button type="submit" class="text-indigo-600 hover:text-indigo-900 font-semibold transition">
                                Changer en {{ $user->role === 'admin' ? 'Client' : 'Admin' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection
