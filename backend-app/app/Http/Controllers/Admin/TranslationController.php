<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Translator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class TranslationController extends Controller
{
    /**
     * Translate English text into Filipino or Cebuano for an admin form. Nothing is saved here.
     */
    public function __invoke(Request $request, Translator $translator): JsonResponse
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:500'],
            'target' => ['required', Rule::in(array_keys(Translator::LANGUAGES))],
        ]);

        try {
            return response()->json(['translation' => $translator->translate($data['text'], $data['target'])]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }
    }
}
