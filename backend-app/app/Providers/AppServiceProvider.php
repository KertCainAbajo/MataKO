<?php

namespace App\Providers;

use App\View\Composers\AdminLayoutComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.admin.layout', AdminLayoutComposer::class);

        // <script @nonce>: lets our own inline scripts run under the Content-Security-Policy.
        Blade::directive('nonce', fn (): string => '<?php echo \'nonce="\'.e(\Illuminate\Support\Facades\Vite::cspNonce()).\'"\'; ?>');

        // Online, every link and redirect uses HTTPS so passwords and tokens are never sent in the clear.
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // App users: at least 8 characters with letters and numbers.
        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        // Sign-in limits per address. Each sign-in page counts separately, so using the app does not use up
        // the admin's attempts on the same network. (The per-account lockout in LoginGuard works on top.)
        foreach (['app-login' => 5, 'admin-login' => 5, 'two-factor' => 5, 'google-login' => 10] as $name => $perMinute) {
            RateLimiter::for($name, fn (Request $request): Limit => Limit::perMinute($perMinute)->by($name.'|'.$request->ip()));
        }

        // Sign-up: a few accounts per minute and per hour from one address, to stop mass account creation.
        RateLimiter::for('register', fn (Request $request): array => [
            Limit::perMinute(3)->by($request->ip()),
            Limit::perHour(10)->by($request->ip()),
        ]);
    }
}
