<?php

namespace App\Providers;

use App\Models\Discount;
use App\Models\Order;
use App\Support\Jalali;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
    }
}
