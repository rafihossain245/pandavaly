<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CourierConsignment;
use App\Services\Courier\CourierDispatcher;
use App\Services\Courier\CourierException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Parcels handed to the courier.
 *
 * The pushing itself lives in App\Services\Courier — this is the window onto
 * it: which orders are with the courier, which failed and why, and which are
 * eligible but have not gone yet. Retrying here goes through the same
 * dispatcher the automatic push and the cron use, so nothing can be shipped
 * twice.
 */
class DeliveryController extends Controller
{
    public function index(Request $request, CourierDispatcher $dispatcher)
    {
        $filter = $request->get('filter', 'all');
        $search = trim((string) $request->get('q'));

        $query = CourierConsignment::with('salesOrder')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('invoice', 'like', "%{$search}%")
                        ->orWhere('consignment_id', 'like', "%{$search}%")
                        ->orWhere('tracking_code', 'like', "%{$search}%")
                        ->orWhere('recipient_phone', 'like', "%{$search}%");
                });
            })
            ->latest('id');

        match ($filter) {
            'failed' => $query->unsent(),
            'moving' => $query->awaitingDelivery(),
            'delivered' => $query->where('delivery_status', 'delivered'),
            default => null,
        };

        $consignments = $query->paginate(20)->withQueryString();

        // Orders that should have a parcel but do not — the list the shop
        // actually has to act on, and the reason this screen exists.
        $waiting = $dispatcher->pendingOrders(20);

        return view('delivery.index', [
            'consignments' => $consignments,
            'waiting' => $waiting,
            'filter' => $filter,
            'search' => $search,
            'counts' => [
                'all' => CourierConsignment::count(),
                'failed' => CourierConsignment::unsent()->count(),
                'moving' => CourierConsignment::awaitingDelivery()->count(),
                'delivered' => CourierConsignment::where('delivery_status', 'delivered')->count(),
            ],
            'courier' => [
                'live' => $dispatcher->client()->isLive(),
                'configured' => $dispatcher->client()->isConfigured(),
                'driver' => $dispatcher->client()->driver(),
                'autoPush' => $dispatcher->autoPushEnabled(),
                'pushStatus' => $dispatcher->pushStatus(),
            ],
        ]);
    }

    /** Retry a consignment the courier never accepted. */
    public function retry(string $role, string $id, CourierDispatcher $dispatcher)
    {
        $consignment = CourierConsignment::with('salesOrder')->findOrFail($id);
        $slug = Str::slug(Auth::user()->getRoleNames()->first());

        if ($consignment->isAccepted()) {
            return redirect()->route('role.delivery.index', ['role' => $slug])
                ->with('success', 'That parcel is already with the courier.');
        }

        if (! $consignment->salesOrder) {
            return redirect()->route('role.delivery.index', ['role' => $slug])
                ->with('error', 'The order behind this consignment no longer exists.');
        }

        try {
            $result = $dispatcher->push($consignment->salesOrder);
        } catch (CourierException $e) {
            return redirect()->route('role.delivery.index', ['role' => $slug])
                ->with('error', 'Courier rejected it again: ' . $e->getMessage());
        }

        return redirect()->route('role.delivery.index', ['role' => $slug])
            ->with('success', 'Accepted — consignment ' . $result->consignment_id . '.');
    }

    /** Ask the courier where a parcel has got to, without waiting for the cron. */
    public function sync(string $role, string $id, CourierDispatcher $dispatcher)
    {
        $consignment = CourierConsignment::findOrFail($id);
        $slug = Str::slug(Auth::user()->getRoleNames()->first());

        $status = $dispatcher->syncStatus($consignment);

        return redirect()->route('role.delivery.index', ['role' => $slug])
            ->with('success', $status
                ? 'Courier reports: ' . ucwords(str_replace('_', ' ', $status)) . '.'
                : 'The courier had no update for that parcel.');
    }
}
