<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * The shop's money, in one place: what came in from orders, what went out as
 * expenses, and the difference.
 *
 * This is a read-only summary over data other screens already own — orders are
 * entered by shoppers, expenses under Accounts → Expenses. Nothing is recorded
 * here, so there is no second version of the truth to reconcile.
 */
class AccountsController extends Controller
{
    /**
     * Orders whose money is actually earned. Anything still in transit could be
     * refused at the door, which for cash on delivery is a real outcome, so it
     * is counted as pipeline rather than revenue.
     */
    private const EARNED_STATUSES = ['delivered', 'completed'];

    /** Never counted either way. */
    private const DEAD_STATUSES = ['cancelled'];

    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);

        $earned = $this->orders($from, $to)
            ->whereIn('status', self::EARNED_STATUSES);

        $pipeline = $this->orders($from, $to)
            ->whereNotIn('status', array_merge(self::EARNED_STATUSES, self::DEAD_STATUSES));

        $revenue = (float) $earned->clone()->sum('total');
        $orderCount = (int) $earned->clone()->count();

        $expenseQuery = Expense::query()
            ->where('status', true)
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()]);

        $expenses = (float) $expenseQuery->clone()->sum('amount');

        // Spend per category, biggest first — the usual first question when the
        // net number looks wrong.
        $byCategory = $expenseQuery->clone()
            ->selectRaw('expense_category_id, SUM(amount) as total')
            ->groupBy('expense_category_id')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) use ($expenses) {
                return [
                    'name' => ExpenseCategory::find($row->expense_category_id)?->name ?? 'Uncategorised',
                    'total' => (float) $row->total,
                    'share' => $expenses > 0 ? round(($row->total / $expenses) * 100) : 0,
                ];
            });

        $recentExpenses = Expense::with('expense_category')
            ->where('status', true)
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('accounts.index', [
            'from' => $from,
            'to' => $to,
            'preset' => $request->get('preset', 'this_month'),
            'revenue' => $revenue,
            'orderCount' => $orderCount,
            'averageOrder' => $orderCount > 0 ? $revenue / $orderCount : 0.0,
            'pipelineTotal' => (float) $pipeline->clone()->sum('total'),
            'pipelineCount' => (int) $pipeline->clone()->count(),
            'expenses' => $expenses,
            'net' => $revenue - $expenses,
            'byCategory' => $byCategory,
            'recentExpenses' => $recentExpenses,
        ]);
    }

    /**
     * Storefront orders only. `company_name` is what separates a real order from
     * the ERP template's rows, and it is the same filter the Orders screen uses,
     * so the two totals agree.
     */
    private function orders(Carbon $from, Carbon $to)
    {
        return SalesOrder::query()
            ->whereNotNull('company_name')
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);
    }

    /**
     * The reporting window. Presets cover the questions actually asked; custom
     * dates are there for everything else.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function range(Request $request): array
    {
        $preset = $request->get('preset', 'this_month');

        if ($preset === 'custom' && $request->filled('from') && $request->filled('to')) {
            try {
                $from = Carbon::parse($request->get('from'));
                $to = Carbon::parse($request->get('to'));

                // A backwards range returns nothing and reads as "no data" rather
                // than as a mistake, so straighten it out instead.
                return $from->lte($to) ? [$from, $to] : [$to, $from];
            } catch (\Throwable) {
                // Fall through to the default window on an unparseable date.
            }
        }

        return match ($preset) {
            'today' => [Carbon::today(), Carbon::today()],
            'last_7' => [Carbon::today()->subDays(6), Carbon::today()],
            'last_month' => [
                Carbon::today()->subMonthNoOverflow()->startOfMonth(),
                Carbon::today()->subMonthNoOverflow()->endOfMonth(),
            ],
            'this_year' => [Carbon::today()->startOfYear(), Carbon::today()],
            default => [Carbon::today()->startOfMonth(), Carbon::today()],
        };
    }
}
