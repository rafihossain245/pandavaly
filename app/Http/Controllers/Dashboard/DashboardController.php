<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OrderController;
use App\Models\Buyer;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\SalesOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /** Stock level at or below which a product lands in the restock panel. */
    private const LOW_STOCK_THRESHOLD = 5;

    /** Rows in the "recent" and "low stock" panels. */
    private const PANEL_ROWS = 6;

    /**
     * Display the Super Admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $prevMonthStart = $monthStart->copy()->subMonthNoOverflow();
        $prevMonthEnd = $monthStart->copy()->subSecond();

        // Each headline number is paired with the same measure over this month and
        // the one before it, so the "% from last month" line is derived rather than
        // decorative.
        $stats = [
            'sales' => $this->metric(
                $this->revenueOrders()->sum('total'),
                $this->revenueOrders()->where('created_at', '>=', $monthStart)->sum('total'),
                $this->revenueOrders()->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])->sum('total'),
            ),
            'orders' => $this->metric(
                $this->storefrontOrders()->count(),
                $this->storefrontOrders()->where('created_at', '>=', $monthStart)->count(),
                $this->storefrontOrders()->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])->count(),
            ),
            'customers' => $this->metric(
                Buyer::count(),
                Buyer::where('created_at', '>=', $monthStart)->count(),
                Buyer::whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])->count(),
            ),
            'products' => $this->metric(
                $this->activeProducts()->count(),
                $this->activeProducts()->where('created_at', '>=', $monthStart)->count(),
                $this->activeProducts()->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])->count(),
            ),
        ];

        return view('panel.dashboard.index', [
            'stats' => $stats,
            'salesChart' => $this->salesLastSevenDays($now),
            'customerChart' => $this->customersLastSixMonths($now),
            'recentOrders' => $this->storefrontOrders()
                ->with('buyer')
                ->latest()
                ->limit(self::PANEL_ROWS)
                ->get(),
            'lowStockProducts' => $this->activeProducts()
                ->where('stock_qty', '<=', self::LOW_STOCK_THRESHOLD)
                ->orderBy('stock_qty')
                ->limit(self::PANEL_ROWS)
                ->get(['id', 'name', 'sku', 'stock_qty', 'thumbnail']),
            'lowStockThreshold' => self::LOW_STOCK_THRESHOLD,
            'pendingOrderCount' => $this->storefrontOrders()->where('status', 'pending')->count(),
            'pendingReviewCount' => ProductReview::where('is_approved', false)->count(),
            'statusColors' => OrderController::STATUS_COLORS,
        ]);
    }

    /**
     * Storefront orders. A non-null `company_name` is what separates a checkout
     * order from the ERP template's own sales orders; the admin order list draws
     * the line the same way, so the dashboard totals agree with that screen.
     *
     * SalesOrder carries a `deleted_at` column but not the SoftDeletes trait, so
     * binned rows have to be excluded by hand or they would still count as revenue.
     */
    private function storefrontOrders(): Builder
    {
        return SalesOrder::query()
            ->whereNotNull('company_name')
            ->whereNull('deleted_at');
    }

    /** Orders that represent money taken — a cancelled order is not revenue. */
    private function revenueOrders(): Builder
    {
        return $this->storefrontOrders()->where('status', '!=', 'cancelled');
    }

    /** Product has a `deleted_at` column but no SoftDeletes trait either. */
    private function activeProducts(): Builder
    {
        return Product::query()
            ->where('is_active', 1)
            ->whereNull('deleted_at');
    }

    /**
     * @return array{total: float|int, current: float|int, previous: float|int, change: float|null}
     */
    private function metric(float|int $total, float|int $current, float|int $previous): array
    {
        return [
            'total' => $total,
            'current' => $current,
            'previous' => $previous,
            // Null, not zero: with no activity last month there is no percentage to
            // quote, and inventing "+100%" would be a lie the card cannot defend.
            'change' => $previous > 0 ? (($current - $previous) / $previous) * 100 : null,
        ];
    }

    /**
     * Revenue per day for the last seven days, zero-filled so the line chart keeps
     * an even x-axis on quiet days.
     *
     * @return array{labels: list<string>, data: list<float>}
     */
    private function salesLastSevenDays(Carbon $now): array
    {
        $totals = $this->revenueOrders()
            ->where('created_at', '>=', $now->copy()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as day, SUM(total) as revenue')
            ->groupBy('day')
            ->pluck('revenue', 'day');

        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $labels[] = $day->format('D');
            $data[] = (float) ($totals[$day->toDateString()] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * New customers per month for the last six months, zero-filled.
     *
     * @return array{labels: list<string>, data: list<int>}
     */
    private function customersLastSixMonths(Carbon $now): array
    {
        $totals = Buyer::query()
            ->where('created_at', '>=', $now->copy()->subMonthsNoOverflow(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonthsNoOverflow($i);
            $labels[] = $month->format('M');
            $data[] = (int) ($totals[$month->format('Y-m')] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
