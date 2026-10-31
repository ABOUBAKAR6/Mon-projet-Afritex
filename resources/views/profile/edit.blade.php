<x-app-layout>
    <x-slot name="header">
        <h1 class="titre-display text-[2.2rem] sm:text-[2.8rem]">Mon profil</h1>
    </x-slot>

    <div class="conteneur max-w-3xl space-y-4 pb-20 pt-10 lg:pb-28">
        <div class="bg-carte p-6 sm:p-8 [&_h2]:titre-display [&_h2]:text-[1.25rem] [&_header_p]:text-encre-2">
            @include('profile.partials.update-profile-information-form')
        </div>
        <div class="bg-carte p-6 sm:p-8 [&_h2]:titre-display [&_h2]:text-[1.25rem] [&_header_p]:text-encre-2">
            @include('profile.partials.update-password-form')
        </div>
        <div class="bg-carte p-6 sm:p-8 [&_h2]:titre-display [&_h2]:text-[1.25rem] [&_header_p]:text-encre-2">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
