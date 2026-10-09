<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan (docs/08 §2.4). CSP masih longgar — diketatkan bertahap.
 * Berlaku untuk semua response PHP; file statis diatur di nginx.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'accelerometer=(), camera=(self), geolocation=(), microphone=(), payment=(), usb=()');
        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());

        return $response;
    }

    private function contentSecurityPolicy(): string
    {
        // Longgar dulu (termasuk HMR Vite di dev); produksi tanpa origin eksternal.
        $scriptSrc = config('app.env') === 'production'
            ? "script-src 'self' 'unsafe-inline' 'unsafe-eval'"
            : "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:*";
        $connectSrc = config('app.env') === 'production'
            ? "connect-src 'self'"
            : "connect-src 'self' ws: wss: http://localhost:*";

        return implode('; ', [
            "default-src 'self'",
            $scriptSrc,
            $connectSrc,
            // Google Fonts (Plus Jakarta Sans) — docs/06. Tanpa ini font diblokir di production.
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "img-src 'self' data: blob:",
            "font-src 'self' data: https://fonts.gstatic.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ]);
    }
}
