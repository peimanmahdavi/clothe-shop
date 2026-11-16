<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class DashboardController
{
    public function __invoke(): View
    {
        $revenue = Order::query()->revenue();
        $todayStart = now()->startOfDay();
        $weekStart = now()->subDays(6)->startOfDay();
        $previousWeekStart = now()->subDays(13)->startOfDay();
        $previousWeekEnd = now()->subDays(7)->endOfDay();
        $monthStart = now()->subDays(29)->startOfDay();
        $previousMonthStart = now()->subDays(59)->startOfDay();
        $previousMonthEnd = now()->subDays(30)->endOfDay();

        $salesToday = (clone $revenue)->where('placed_at', '>=', $todayStart)->sum('total');
        $salesWeek = (clone $revenue)->where('placed_at', '>=', $weekStart)->sum('total');
        $salesPreviousWeek = (clone $revenue)
            ->whereBetween('placed_at', [$previousWeekStart, $previousWeekEnd])
            ->sum('total');
        $salesMonth = (clone $revenue)->where('placed_at', '>=', $monthStart)->sum('total');
        $salesPreviousMonth = (clone $revenue)
            ->whereBetween('placed_at', [$previousMonthStart, $previousMonthEnd])
            ->sum('total');

        $ordersToday = Order::query()->where('placed_at', '>=', $todayStart)->count();
        $pendingOrders = Order::query()->where('status', 'pending');
        $pendingCount = (clone $pendingOrders)->count();
        $pendingTotal = (clone $pendingOrders)->sum('total');

        $growth = static function (int $current, int $previous): ?int {
            if ($previous === 0) {
                return $current > 0 ? 100 : null;
            }

            return (int) round((($current - $previous) / $previous) * 100);
        };

        $chartOrders = Order::query()
            ->revenue()
            ->where('placed_at', '>=', now()->subDays(11)->startOfDay())
            ->get(['placed_at', 'total']);

        $chart = collect(range(11, 0))->map(function (int $daysAgo) use ($chartOrders): array {
            $date = Carbon::today()->subDays($daysAgo);

            return [
                'label' => $date->format('Y-m-d'),
                'total' => $chartOrders
                    ->filter(fn (Order $order): bool => $order->placed_at->isSameDay($date))
                    ->sum('total'),
            ];
        })->all();

        $statusCounts = Order::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return view('admin.dashboard', [
            'salesToday' => (int) $salesToday,
            'salesWeek' => (int) $salesWeek,
            'salesMonth' => (int) $salesMonth,
            'weekGrowth' => $growth((int) $salesWeek, (int) $salesPreviousWeek),
            'monthGrowth' => $growth((int) $salesMonth, (int) $salesPreviousMonth),
            'ordersToday' => $ordersToday,
            'pendingCount' => $pendingCount,
            'pendingTotal' => (int) $pendingTotal,
            'chart' => $chart,
            'statusCounts' => $statusCounts,
            'ordersCount' => $statusCounts->sum(),
            'recentOrders' => Order::query()->latest('placed_at')->take(5)->get(),
            'lowStock' => Product::query()->where('stock', '<=', 5)->with('images')->orderBy('stock')->take(4)->get(),
            'outOfStock' => Product::query()->where('stock', 0)->count(),
        ]);
    }
}
