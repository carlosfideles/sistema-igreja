<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adiciona cabeçalhos HTTP de segurança em todas as respostas.
 * Protege contra: Clickjacking, MIME sniffing, XSS, e vazamento de referrer.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Impede que a página seja exibida em iframes (proteção contra Clickjacking)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Impede que o navegador "adivinhe" o tipo MIME (proteção contra MIME sniffing)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Ativa o filtro XSS do navegador em modo de bloqueio
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Controla quais informações de referrer são enviadas com as requisições
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Impede acesso a recursos sensíveis pelo navegador sem permissão explícita (permite câmera para selfies do sistema)
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()');

        // Remove informações sobre a tecnologia utilizada no servidor
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
