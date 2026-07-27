<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::withCount('stocks')->orderBy('name')->paginate(20);
        return view('warehouses.index', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:ware_houses,code',
        ]);

        Warehouse::create([
            'name'       => $request->name,
            'code'       => strtoupper($request->code),
            'company_id' => 1,
        ]);

        return response()->json(['success' => true, 'message' => 'Warehouse created successfully.']);
    }

    public function edit(string $_role, int $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        return response()->json(['success' => true, 'data' => $warehouse]);
    }

    public function update(Request $request, int $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:ware_houses,code,' . $id,
        ]);

        $warehouse->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
        ]);

        return response()->json(['success' => true, 'message' => 'Warehouse updated successfully.']);
    }

    public function destroy(Request $request, $_id = null)
    {
        $warehouse = Warehouse::findOrFail($request->item_id);

        if ($warehouse->stocks()->exists()) {
            return response()->json(['success' => false, 'message' => 'Cannot delete warehouse with existing stock records.']);
        }

        $warehouse->delete();
        return response()->json(['success' => true, 'message' => 'Warehouse deleted.']);
    }
}
