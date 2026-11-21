<?php

namespace App\Providers;

use App\Models\Discount;
use App\Models\Order;
use App\Services\CartService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        Blade::directive('jalali', function (string $expression): string {
            return "<?php echo \\App\\Support\\Jalali::format({$expression}); ?>";
        });

        View::composer('layouts.admin', function (\Illuminate\View\View $view): void {
            $view->with('pendingOrdersCount', Order::query()->where('status', 'pending')->count());
            $view->with('activeCampaign', Discount::query()->running()->orderBy('ends_at')->first());
        });

        View::composer('layouts.app', function (\Illuminate\View\View $view): void {
            $view->with('cartCount', app(CartService::class)->count());
        });

        RateLimiter::for('global', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(200)->by($request->user()->id)
                : Limit::perMinute(60)->by($request->ip());
        });

        // Authentication + OTP endpoints: key by both the account identifier
        // and IP so neither a distributed attack nor one noisy neighbour can
        // lock out unrelated users.
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)->by(Str::transliterate(
                Str::lower($request->string('phone')->toString()).'|'.$request->ip(),
            ));
        });

        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(5)->by(Str::transliterate(
                Str::lower($request->string('phone')->toString()).'|'.$request->ip(),
            ));
        });

        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(5)->by('user:'.($request->user()?->id ?? $request->ip()));
        });

        // Expensive state-changing customer actions.
        RateLimiter::for('orders', function (Request $request) {
            return Limit::perMinute(10)->by('user:'.($request->user()?->id ?? $request->ip()));
        });

        RateLimiter::for('receipts', function (Request $request) {
            return Limit::perMinute(5)->by('user:'.($request->user()?->id ?? $request->ip()));
        });
    }
}
