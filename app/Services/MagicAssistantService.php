<?php

namespace App\Services;

use App\Models\Produit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MagicAssistantService
{
    protected string $apiKey;
    protected string $apiUrl = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.key');
    }

    public function getSuggestions(string $userPrompt): array
    {
        if (empty($this->apiKey)) {
            return [
                'message' => 'L\'assistant IA est actuellement en maintenance. Mais je vous suggère ces produits !',
                'suggestions' => Produit::inRandomOrder()->take(3)->get()
            ];
        }

        try {
            $productsContext = $this->getProductsContext();

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => 'claude-3-5-sonnet-20240620',
                'max_tokens' => 1024,
                'system' => "Tu es l'Assistant Magique d'".boutique('nom').", le conseiller expert en textiles et tissus les plus prestigieux.
                            Ton but est de transformer une simple demande en une expérience d'achat luxueuse.
                            Tu ne te contentes pas de donner des IDs, tu conseilles sur le style, l'occasion (mariage, travail, quotidien) et la qualité.

                            Voici le catalogue actuel des produits disponibles :
                            {$productsContext}

                            CONSIGNES DE VENTE :
                            1. Réponds toujours en français, avec élégance et enthousiasme.
                            2. Analyse le besoin latent : si le client veut un tissu pour un mariage, suggère des matières nobles et brillantes.
                            3. Valorise les produits : explique POURQUOI ce tissu est le meilleur choix pour lui.
                            4. Format de réponse :
                               - Un conseil personnalisé et vendeur.
                               - Une recommandation claire.
                               - À la fin, suggère 1 à 3 IDs de produits pertinents sous la forme : [IDS: 1, 2, 3].

                            Si aucun produit ne correspond parfaitement, propose l'alternative la plus proche et explique pourquoi.",
                'messages' => [
                    ['role' => 'user', 'content' => $userPrompt],
                ],
            ]);

            if ($response->failed()) {
                throw new \Exception('Erreur lors de l\'appel à l\'API Claude : ' . $response->body());
            }

            $content = $response->json('content.0.text');

            // Extraire les IDs suggérés : [IDS: 1, 2, 3]
            preg_match('/\[IDS: (.*?)\]/', $content, $matches);
            $productIds = isset($matches[1]) ? explode(',', $matches[1]) : [];

            // Nettoyer le message pour ne pas afficher les IDs techniques au client
            $cleanMessage = preg_replace('/\[IDS: .*?\]/', '', $content);

            $suggestions = Produit::whereIn('id', array_map('trim', $productIds))->get();

            // Si l'IA n'a rien suggéré ou si les produits n'existent pas, on en prend quelques-uns au hasard
            if ($suggestions->isEmpty()) {
                $suggestions = Produit::inRandomOrder()->take(3)->get();
            }

            return [
                'message' => trim($cleanMessage),
                'suggestions' => $suggestions
            ];

        } catch (\Exception $e) {
            Log::error('MagicAssistantService Error: ' . $e->getMessage());
            return [
                'message' => 'Désolé, mon esprit magique est un peu fatigué... mais voici quelques suggestions !',
                'suggestions' => Produit::inRandomOrder()->take(3)->get()
            ];
        }
    }

    public function generateProductDescription(Produit $produit, array $keywords): string
    {
        if (empty($this->apiKey)) return "Configuration IA manquante.";

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => 'claude-3-5-sonnet-20240620',
                'max_tokens' => 500,
                'system' => "Tu es un expert en copywriting pour le textile. Ton but est de rédiger des descriptions de produits captivantes, luxueuses et vendeuses.",
                'messages' => [
                    ['role' => 'user', 'content' => "Rédige une description pour le produit suivant : {$produit->nom}. Mots-clés à inclure : " . implode(', ', $keywords)],
                ],
            ]);

            return $response->json('content.0.text');
        } catch (\Exception $e) {
            Log::error('AI Description Error: ' . $e->getMessage());
            return "Erreur lors de la génération.";
        }
    }

    public function analyzeReviewSentiment(string $reviewText): array
    {
        if (empty($this->apiKey)) return ['sentiment' => 'unknown', 'summary' => 'IA non configurée'];

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => 'claude-3-5-sonnet-20240620',
                'max_tokens' => 200,
                'system' => "Tu es un analyste de satisfaction client. Analyse le sentiment de l'avis suivant et réponds UNIQUEMENT au format JSON : {\"sentiment\": \"positif\"|\"negatif\"|\"neutre\", \"summary\": \"un résumé court de la plainte ou du compliment\"}",
                'messages' => [
                    ['role' => 'user', 'content' => $reviewText],
                ],
            ]);

            return json_decode($response->json('content.0.text'), true) ?? ['sentiment' => 'unknown', 'summary' => 'Erreur analyse'];
        } catch (\Exception $e) {
            Log::error('AI Sentiment Error: ' . $e->getMessage());
            return ['sentiment' => 'unknown', 'summary' => 'Erreur technique'];
        }
    }

    protected function getProductsContext(): string
    {
        $products = Produit::with('categorie')->all();
        $context = "";
        foreach ($products as $p) {
            $colors = is_array($p->couleurs) ? implode(', ', $p->couleurs) : ($p->couleurs ?? 'Non spécifié');
            $catName = $p->categorie ? $p->categorie->nom : 'Inconnue';
            $context .= "- ID: {$p->id}, Nom: {$p->nom}, Catégorie: {$catName}, Couleurs: {$colors}, Prix/m: {$p->prix_metre} F\n";
        }
        return $context;
    }
}
