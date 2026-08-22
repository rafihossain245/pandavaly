<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Delivery charge per district — the number the funnel adds to every order and
 * the one a shop changes most often (a fuel price move, a new courier rate).
 * It was seeded straight into the districts table with no screen behind it, so
 * changing it meant SQL. This is that screen.
 *
 * Districts are never deleted here: orders reference them, and a deleted row
 * would take the delivery address off an old invoice. An area the shop has
 * stopped serving is switched off instead, which removes it from the funnel's
 * district picker while leaving past orders intact.
 */
class DeliveryAreaController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('q'));

        $districts = District::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('name_bn', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('delivery-areas.index', [
            'districts' => $districts,
            'search' => $search,
            'defaultCharge' => District::DEFAULT_DELIVERY_CHARGE,
        ]);
    }

    /**
     * Saves the whole visible table in one submit. Only the rows that were
     * posted are touched, so saving a filtered list cannot switch off every
     * district that the search happened to hide.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'charges' => 'required|array',
            'charges.*' => 'required|numeric|min:0|max:99999',
        ], [
            'charges.required' => 'There was nothing to save.',
            'charges.*.required' => 'Every area needs a delivery charge — use 0 for free delivery.',
            'charges.*.numeric' => 'A delivery charge must be a number, in taka.',
        ]);

        // Unchecked boxes are not posted at all, so absence is the "off" value —
        // but only for the rows that were on screen.
        $active = collect($request->input('active', []))->keys()->map('intval')->all();

        $districts = District::whereIn('id', array_keys($validated['charges']))->get();

        foreach ($districts as $district) {
            $district->update([
                'delivery_charge' => $validated['charges'][$district->id],
                'is_active' => in_array($district->id, $active, true),
            ]);
        }

        return redirect()
            ->back()
            ->with('success', $districts->count() . ' delivery ' . Str::plural('area', $districts->count()) . ' updated.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:districts,name',
            'name_bn' => 'nullable|string|max:100',
            'delivery_charge' => 'required|numeric|min:0|max:99999',
        ], [
            'name.unique' => 'That area is already in the list — search for it and edit its charge instead.',
        ]);

        District::create($validated + [
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => (int) District::max('sort_order') + 1,
        ]);

        return redirect()->back()->with('success', 'Delivery area added.');
    }
}
