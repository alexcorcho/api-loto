<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class WordPressAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = config('services.wordpress.api_token');

        // Validar el token
        $validateResponse = Http::withToken($token)
            ->get(config('services.wordpress.api_url') . '/jwt-auth/v1/token/validate');

        if ($validateResponse->successful()) {
            return $next($request);
        }

        // Intentar refrescar el token si es inválido
        $refreshResponse = Http::withToken($token)
            ->post(config('services.wordpress.api_url') . '/jwt-auth/v1/token/refresh');

        if ($refreshResponse->successful()) {
            $newToken = $refreshResponse->json()['token'];

            // Actualizar el .env dinámicamente
            file_put_contents(base_path('.env'), preg_replace(
                "/WP_API_TOKEN=.*/",
                "WP_API_TOKEN={$newToken}",
                file_get_contents(base_path('.env'))
            ));

            Log::info('Token de WordPress refrescado automáticamente.');

            // Actualizar en tiempo de ejecución
            config(['services.wordpress.api_token' => $newToken]);

            return $next($request);
        }

        // Si no se puede refrescar, devolver error
        return response()->json(['error' => 'No se pudo autenticar con WordPress'], Response::HTTP_UNAUTHORIZED);
    }

    
}
