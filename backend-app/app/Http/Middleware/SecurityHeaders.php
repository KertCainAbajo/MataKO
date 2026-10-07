<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Browser-side protection on every response: blocks clickjacking, MIME sniffing and, through the
 * Content-Security-Policy, any script that is not ours (a script injected into a page will not run).
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Every inline <script> in our pages carries this per-request nonce; anything else is blocked.
        Vite::useCspNonce();

        $response = $next($request);
        $headers = $response->headers;

        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'DENY');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $headers->remove('X-Powered-By');
        header_remove('X-Powered-By');

        if ($request->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        if (str_contains((string) $headers->get('Content-Type'), 'text/html')) {
            $headers->set('Content-Security-Policy', $this->policy());
        }

        return $response;
    }

    private function policy(): string
    {
        $nonce = Vite::cspNonce();
        // The Vite dev server (npm run dev) serves scripts and live reload from its own port.
        $dev = app()->isLocal() ? ' http://localhost:5173 http://127.0.0.1:5173 ws://localhost:5173 ws://127.0.0.1:5173' : '';

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}'{$dev}",
            // Tailwind utility classes need no inline styles, but a few style="" attributes remain.
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net{$dev}",
            "font-src 'self' https://fonts.bunny.net",
            "img-src 'self' data: blob:",
            "connect-src 'self'{$dev}",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "object-src 'none'",
        ]);
    }
}
