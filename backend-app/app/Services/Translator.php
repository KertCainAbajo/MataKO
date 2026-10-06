<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Machine translation through MyMemory (https://mymemory.translated.net): free, no key, HTTPS only.
 * Used by the admin to draft Filipino and Cebuano text that an admin reviews before saving.
 */
class Translator
{
    /** @var array<string, string> Admin language codes mapped to MyMemory language codes. */
    public const LANGUAGES = ['fil' => 'fil', 'ceb' => 'ceb'];

    public function translate(string $text, string $language): string
    {
        $response = Http::timeout(15)->retry(1, 500, throw: false)->get('https://api.mymemory.translated.net/get', array_filter([
            'q' => $text,
            'langpair' => 'en|'.self::LANGUAGES[$language],
            // An email raises the free daily limit from about 5,000 to 50,000 characters.
            'de' => config('services.mymemory.email'),
        ]));

        $data = $response->json() ?? [];
        $translated = $data['responseData']['translatedText'] ?? null;

        if (! $response->successful() || ($data['responseStatus'] ?? 200) != 200 || ! is_string($translated) || $translated === '') {
            throw new RuntimeException(($data['quotaFinished'] ?? false)
                ? 'The free daily translation limit has been reached. Try again tomorrow.'
                : 'The translation service is not available right now. Try again in a moment.');
        }
        if (str_contains($translated, 'MYMEMORY WARNING')) {
            throw new RuntimeException('The free daily translation limit has been reached. Try again tomorrow.');
        }

        return html_entity_decode($translated, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
