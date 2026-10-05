<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromRequest
{
    /** @var list<string> Languages the API can answer in besides English. */
    private const SUPPORTED = ['fil', 'ceb'];

    /**
     * Answer in the language the app asks for with its Accept-Language header ("fil" or "ceb").
     */
    public function handle(Request $request, Closure $next): Response
    {
        $language = strtolower(substr((string) $request->header('Accept-Language'), 0, 3));
        $language = rtrim($language, '-_,;');

        if (in_array($language, self::SUPPORTED, true)) {
            App::setLocale($language);
        }

        return $next($request);
    }
}
