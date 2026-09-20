<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use App\Models\Categorie;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $products = Produit::where('disponible', 1)->get();
        $categories = Categorie::all();

        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Home page
        $sitemap .= '<url>' . "\n";
        $sitemap .= '  <loc>' . url('/') . '</loc>' . "\n";
        $sitemap .= '  <changefreq>daily</changefreq>' . "\n";
        $sitemap .= '  <priority>1.0</priority>' . "\n";
        $sitemap .= '</url>' . "\n";

        // Categories
        foreach ($categories as $category) {
            $sitemap .= '<url>' . "\n";
            $sitemap .= '  <loc>' . url('/catalogue?categorie_id=' . $category->id) . '</loc>' . "\n";
            $sitemap .= '  <changefreq>weekly</changefreq>' . "\n";
            $sitemap .= '  <priority>0.8</priority>' . "\n";
            $sitemap .= '</url>' . "\n";
        }

        // Products
        foreach ($products as $product) {
            $sitemap .= '<url>' . "\n";
            $sitemap .= '  <loc>' . url('/produit/' . $product->slug) . '</loc>' . "\n";
            $sitemap .= '  <changefreq>weekly</changefreq>' . "\n";
            $sitemap .= '  <priority>0.6</priority>' . "\n";
            $sitemap .= '</url>' . "\n";
        }

        $sitemap .= '</urlset>';

        return response($sitemap)->header('Content-Type', 'text/xml');
    }
}
