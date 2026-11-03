<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Tip;
use Illuminate\View\View;

/**
 * The public home page that presents MataKo: what it does, screenshots, the admin console and how it is built.
 */
class LandingController extends Controller
{
    public function __invoke(): View
    {
        // Live figures from the database; if it is unreachable the page still shows sensible numbers.
        $stats = rescue(fn (): array => [
            'questions' => Question::forAudience('student')->count() ?: 16,
            'exercises' => Tip::where('kind', 'exercise')->where('is_active', true)->count() ?: 5,
            'topics' => Tip::where('kind', 'topic')->where('is_active', true)->count() ?: 3,
        ], ['questions' => 16, 'exercises' => 5, 'topics' => 3], report: false);

        return view('landing', [
            'stats' => $stats,
            'apkUrl' => config('services.showcase.apk_url'),
        ]);
    }
}
