<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Supplier::select('suppliers.*')->orderBy('suppliers.name', 'asc');

        if ($request->filled('name')) {
            $query->where('suppliers.name', $request->name);
        }

        if ($request->filled('email')) {
            $query->where('suppliers.email', $request->email);
        }

        if ($request->filled('phone')) {
            $query->where('suppliers.phone', $request->phone);
        }

        if ($request->filled('is_active')) {
            $query->whereDate('suppliers.is_active', $request->is_active);
        }

        $datas = $query->paginate(20);

        return view('suppliers.index', compact('datas'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('suppliers.create-modal');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'name'     => 'required|string|max:255|unique:suppliers,name',
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $validator->errors()->first()
                    ]);
                }

                return redirect()->back()->withErrors($validator)->withInput();
            }

            $data = Supplier::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'is_active' => $request->is_active ? 1 : 0
            ]);

            $bank_accounts = $request->input('bank_accounts', []);
            if (!empty($bank_accounts)) {
                foreach ($bank_accounts as $account) {
                    $data->bankAccounts()->create([
                        'bank_name' => $account['bank_name'],
                        'branch' => $account['branch'] ?? null,
                        'account_name' => $account['account_name'],
                        'account_no' => $account['account_no'],
                        'swift' => $account['swift'] ?? null,
                    ]);
                }
            }


            DB::commit();
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data created successfully.',
                    'data' => $data
                ]);
            }

            return redirect()->route('role.suppliers.index')->with('success', 'Data created successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('suppliers.edit-modal', compact('id'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $id = $request->id;
            $data = Supplier::findOrFail($id);
            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Info Not Found!'
                ]);
            }
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:suppliers,name,' . $data->id
            ]);

            $data->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'is_active' => $request->is_active ? 1 : 0
            ]);
            $bank_accounts = $request->input('bank_accounts', []);
            $data->bankAccounts()->delete();
            foreach ($bank_accounts as $account) {
                $data->bankAccounts()->create([
                    'bank_name' => $account['bank_name'],
                    'branch' => $account['branch'] ?? null,
                    'account_name' => $account['account_name'],
                    'account_no' => $account['account_no'],
                    'swift' => $account['swift'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Data updated successfully.',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $item = Supplier::find($request->item_id);
            if ($item) {
                $item->delete();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Info Not Found!'
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data deleted successfully.'
        ]);
    }
    public function getBankAccounts($role, $id)
    {
        $supplier = Supplier::with('bankAccounts')->find($id);
        if (!$supplier) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier not found.'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $supplier->bankAccounts
        ]);
    }
}
