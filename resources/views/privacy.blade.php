@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <h1 class="text-4xl font-bold mb-8">📋 Politique de Confidentialité & Cookies</h1>

    <div class="prose prose-lg max-w-none space-y-6">
        <!-- Cookies -->
        <section>
            <h2 class="text-2xl font-bold mb-4">🍪 Gestion des Cookies</h2>
            
            <h3 class="text-xl font-semibold mb-2">1. Types de Cookies</h3>
            <p class="text-gray-700 mb-4">
                Nous utilisons 3 catégories de cookies :
            </p>
            <ul class="list-disc list-inside space-y-2 text-gray-700 mb-4">
                <li><strong>Cookies Essentiels</strong> : Panier, session utilisateur, authentification. Obligatoires pour le fonctionnement du site.</li>
                <li><strong>Cookies de Préférences</strong> : Langue choisie, thème, filtres favoris. Stockent vos préférences personnelles.</li>
                <li><strong>Cookies d'Analytics</strong> : Suivi anonyme des visites pour améliorer le site. Optionnels.</li>
            </ul>

            <h3 class="text-xl font-semibold mb-2">2. Panier Persistant</h3>
            <p class="text-gray-700 mb-4">
                Votre panier est sauvegardé localement sur votre appareil. Cela vous permet de :
            </p>
            <ul class="list-disc list-inside space-y-2 text-gray-700">
                <li>Retrouver vos articles même après fermeture du navigateur</li>
                <li>Continuer vos achats sur plusieurs sessions</li>
                <li>Synchroniser votre panier entre plusieurs appareils (si connecté)</li>
            </ul>
        </section>

        <!-- Préférences -->
        <section>
            <h2 class="text-2xl font-bold mb-4">💾 Préférences Utilisateur</h2>
            
            <p class="text-gray-700 mb-4">
                Nous mémorisons les préférences suivantes :
            </p>
            <ul class="list-disc list-inside space-y-2 text-gray-700">
                <li>Langue de navigation (Français/Anglais)</li>
                <li>Thème (clair/sombre si disponible)</li>
                <li>Catégories favorites</li>
                <li>Filtres de recherche préférés</li>
            </ul>
        </section>

        <!-- Données Personnelles -->
        <section>
            <h2 class="text-2xl font-bold mb-4">🔒 Données Personnelles</h2>
            
            <h3 class="text-xl font-semibold mb-2">Informations Collectées</h3>
            <p class="text-gray-700 mb-4">
                Lors de votre inscription ou achat, nous collectons :
            </p>
            <ul class="list-disc list-inside space-y-2 text-gray-700">
                <li>Nom, email, téléphone</li>
                <li>Adresse de livraison</li>
                <li>Historique des commandes</li>
                <li>Informations de paiement (via FedaPay, pas stockées chez nous)</li>
            </ul>

            <h3 class="text-xl font-semibold mb-2 mt-6">Utilisation des Données</h3>
            <p class="text-gray-700 mb-4">
                Vos données sont utilisées pour :
            </p>
            <ul class="list-disc list-inside space-y-2 text-gray-700">
                <li>Traiter et livrer vos commandes</li>
                <li>Vous envoyer des confirmations de commande</li>
                <li>Améliorer nos services</li>
                <li>Respecter les obligations légales</li>
            </ul>
        </section>

        <!-- Vos Droits -->
        <section>
            <h2 class="text-2xl font-bold mb-4">⚖️ Vos Droits</h2>
            
            <p class="text-gray-700 mb-4">
                Conformément au RGPD et aux lois locales, vous avez le droit de :
            </p>
            <ul class="list-disc list-inside space-y-2 text-gray-700">
                <li>Accéder à vos données personnelles</li>
                <li>Demander la rectification de données inexactes</li>
                <li>Demander la suppression de vos données</li>
                <li>Vous opposer à l'utilisation de vos données</li>
                <li>Exporter vos données dans un format lisible</li>
            </ul>

            <p class="text-gray-700 mt-4">
                Pour exercer ces droits, contactez-nous à <strong>privacy@afritex.ci</strong>
            </p>
        </section>

        <!-- Sécurité -->
        <section>
            <h2 class="text-2xl font-bold mb-4">🛡️ Sécurité</h2>
            
            <p class="text-gray-700 mb-4">
                Nous prenons la sécurité de vos données au sérieux :
            </p>
            <ul class="list-disc list-inside space-y-2 text-gray-700">
                <li>Chiffrement SSL/TLS de toutes les communications</li>
                <li>Stockage sécurisé des mots de passe (hachage bcrypt)</li>
                <li>Aucun stockage des données de paiement (déléguées à FedaPay)</li>
                <li>Audits de sécurité réguliers</li>
            </ul>
        </section>

        <!-- Contact -->
        <section>
            <h2 class="text-2xl font-bold mb-4">📧 Nous Contacter</h2>
            
            <p class="text-gray-700 mb-4">
                Pour toute question concernant cette politique :
            </p>
            <div class="bg-gray-100 p-4 rounded-lg space-y-2">
                <p><strong>Email :</strong> privacy@afritex.ci</p>
                <p><strong>Téléphone :</strong> +225 XX XX XX XX</p>
                <p><strong>Adresse :</strong> [Votre adresse en Côte d'Ivoire]</p>
            </div>
        </section>

        <!-- Dernière Mise à Jour -->
        <section class="text-sm text-gray-600 border-t pt-4 mt-8">
            <p>
                <strong>Dernière mise à jour :</strong> {{ now()->format('d M Y') }}<br>
                Cette politique peut être modifiée à tout moment. Les modifications seront publiées sur cette page.
            </p>
        </section>
    </div>

    <div class="mt-8 text-center">
        <a href="{{ route('accueil') }}" class="inline-block bg-afri-gold text-white px-6 py-3 rounded-lg font-bold hover:bg-afri-brown transition">
            ← Retour à l'accueil
        </a>
    </div>
</div>
@endsection
