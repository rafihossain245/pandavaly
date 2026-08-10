@extends('layout.app')

@section('meta-information')
    <title>Accounts</title>
@endsection

@section('main-content')
    @php
        $roleSlug = Str::slug(Auth::user()->getRoleNames()->first());

        $presets = [
            'today' => 'Today',
            'last_7' => 'Last 7 days',
            'this_month' => 'This month',
            'last_month' => 'Last month',
            'this_year' => 'This year',
        ];

        $tk = fn ($amount) => '৳' . number_format($amount, 2);
    @endphp

    <div class="mx-auto max-w-[1440px]">
        <div class="mb-5 overflow-hidden rounded-xl shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 bg-gradient-to-r from-blue-900 to-blue-800 px-6 py-4">
                <div>
                    <h2 class="text-xl font-semibold text-white">
                        <i class="fas fa-wallet mr-2"></i> Accounts
                    </h2>
                    <p class="mt-0.5 text-xs text-blue-200">
                        {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}
                    </p>
                </div>
                <a href="{{ route('role.expenses.index', ['role' => $roleSlug]) }}"
                    class="rounded-lg bg-white px-4 py-2 text-sm font-medium text-blue-800 transition hover:bg-blue-50">
                    <i class="fas fa-plus mr-1"></i> Record an expense
                </a>
            </div>
        </div>

        {{-- Date range --}}
        <form method="GET" class="mb-5 flex flex-wrap items-end gap-2 rounded-xl border border-gray-200 bg-white p-4">
            @foreach ($presets as $key => $label)
                <a href="{{ route('role.accounts.index', ['role' => $roleSlug, 'preset' => $key]) }}"
                    class="rounded-lg border px-3 py-1.5 text-xs font-medium transition
                        {{ $preset === $key ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">
                    {{ $label }}
                </a>
            @endforeach

            <span class="mx-2 h-6 w-px bg-gray-200"></span>

            <input type="hidden" name="preset" value="custom">
            <div>
                <label for="from" class="block text-xs text-gray-500">From</label>
                <input type="date" name="from" id="from" value="{{ $from->toDateString() }}"
                    class="mt-0.5 rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label for="to" class="block text-xs text-gray-500">To</label>
                <input type="date" name="to" id="to" value="{{ $to->toDateString() }}"
                    class="mt-0.5 rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <button type="submit"
                class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-800">
                Apply
            </button>
        </form>

        {{-- Headline figures --}}
        <div class="mb-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <div class="text-xs uppercase tracking-wide text-gray-500">Revenue</div>
                <div class="mt-1 text-2xl font-semibold text-emerald-600">{{ $tk($revenue) }}</div>
                <div class="mt-1 text-xs text-gray-500">{{ $orderCount }} delivered orders</div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <div class="text-xs uppercase tracking-wide text-gray-500">Expenses</div>
                <div class="mt-1 text-2xl font-semibold text-red-600">{{ $tk($expenses) }}</div>
                <div class="mt-1 text-xs text-gray-500">{{ $byCategory->count() }} categories</div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <div class="text-xs uppercase tracking-wide text-gray-500">Net</div>
                <div class="mt-1 text-2xl font-semibold {{ $net >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                    {{ $tk($net) }}
                </div>
                <div class="mt-1 text-xs text-gray-500">Revenue minus expenses</div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <div class="text-xs uppercase tracking-wide text-gray-500">Average order</div>
                <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $tk($averageOrder) }}</div>
                <div class="mt-1 text-xs text-gray-500">Across delivered orders</div>
            </div>
        </div>

        {{-- Money not yet earned. Kept out of the figures above deliberately, and
             said so here, otherwise the revenue number reads as too low. --}}
        <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <div class="text-sm font-semibold text-amber-900">
                        {{ $tk($pipelineTotal) }} in orders still on the way
                    </div>
                    <p class="mt-0.5 text-xs text-amber-800">
                        {{ $pipelineCount }} orders placed but not delivered yet. Cash on delivery can still be
                        refused at the door, so this is not counted as revenue until the order is marked delivered.
                    </p>
                </div>
                <a href="{{ route('role.orders.index', ['role' => $roleSlug]) }}"
                    class="rounded-lg border border-amber-300 bg-white px-3 py-1.5 text-xs font-medium text-amber-900 transition hover:bg-amber-100">
                    View orders
                </a>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            {{-- Where the money went --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="border-b border-gray-100 px-5 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Spending by category</h3>
                </div>
                <div class="p-5">
                    @forelse ($byCategory as $row)
                        <div class="mb-4 last:mb-0">
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-800">{{ $row['name'] }}</span>
                                <span class="text-gray-600">{{ $tk($row['total']) }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-blue-500" style="width: {{ $row['share'] }}%"></div>
                            </div>
                            <div class="mt-0.5 text-xs text-gray-400">{{ $row['share'] }}% of spending</div>
                        </div>
                    @empty
                        <p class="py-8 text-center text-sm text-gray-500">
                            No expenses recorded in this period.
                        </p>
                    @endforelse
                </div>
            </div>

            {{-- Recent expenses --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Latest expenses</h3>
                    <a href="{{ route('role.expenses.index', ['role' => $roleSlug]) }}"
                        class="text-xs text-blue-600 hover:underline">See all</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                <th class="px-5 py-3 font-semibold">Date</th>
                                <th class="px-5 py-3 font-semibold">Title</th>
                                <th class="px-5 py-3 font-semibold">Category</th>
                                <th class="px-5 py-3 text-right font-semibold">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentExpenses as $expense)
                                <tr class="border-b border-gray-100 last:border-b-0">
                                    <td class="px-5 py-3 text-gray-600">
                                        {{ \Illuminate\Support\Carbon::parse($expense->expense_date)->format('d M') }}
                                    </td>
                                    <td class="px-5 py-3 font-medium text-gray-900">{{ $expense->title }}</td>
                                    <td class="px-5 py-3 text-gray-600">
                                        {{ $expense->expense_category->name ?? '—' }}
                                    </td>
                                    <td class="px-5 py-3 text-right text-gray-900">{{ $tk($expense->amount) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-12 text-center">
                                        <i class="fas fa-receipt mb-3 text-3xl text-gray-300"></i>
                                        <p class="text-gray-500">Nothing recorded in this period.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <p class="mt-4 text-xs text-gray-500">
            Figures cover storefront orders only, using the same filter as the Orders screen, so the two agree.
        </p>
    </div>
@endsection
