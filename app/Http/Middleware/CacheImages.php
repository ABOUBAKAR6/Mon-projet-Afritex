<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheImages
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Si c'est une image, ajouter les en-têtes de cache
        if ($this->isImage($request)) {
            $response->header('Cache-Control', 'public, max-age=31536000, immutable'); // 1 an
            $response->header('Expires', gmdate('D, d M Y H:i:s T', time() + 31536000));
            $response->header('ETag', md5($response->getContent()));
        }

        return $response;
    }

    /**
     * Vérifier si la requête est pour une image
     */
    private function isImage(Request $request): bool
    {
        $path = $request->getPathInfo();
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        
        foreach ($imageExtensions as $ext) {
            if (str_ends_with(strtolower($path), ".$ext")) {
                return true;
            }
        }
        
        return false;
    }
}
