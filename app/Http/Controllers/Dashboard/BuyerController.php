<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuyerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Buyer $buyer)
    {
        try {
            $query = Buyer::select('buyers.*')->orderBy('buyers.business_name', 'desc');

            if ($request->filled('business_name')) {
                $query->where('buyers.business_name', $request->business_name);
            }

            if ($request->filled('email')) {
                $query->where('buyers.email', $request->email);
            }

            if ($request->filled('phone')) {
                $query->where('buyers.phone', $request->phone);
            }

            if ($request->filled('is_active')) {
                $query->whereDate('buyers.is_active', $request->is_active);
            }


            $datas = $query->paginate(20);

            return view('buyers.index', compact('datas'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while loading the buyers page.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // validate everything from your create modal
        $validated = $request->validate([
            'business_name'      => 'required|string|max:255',
            'category'           => 'nullable|string|max:255',
            'email'              => 'nullable|email|max:255',
            'phone'              => 'nullable|string|max:30',
            'tin'                => 'nullable|string|max:100',
            'trade_license_no'   => 'nullable|string|max:150',


            'contacts.*.name'        => 'nullable|string|max:255',
            'contacts.*.email'       => 'nullable|email|max:255',
            'contacts.*.phone'       => 'nullable|string|max:50',
            'contacts.*.designation' => 'nullable|string|max:100',
            'primary_contact_index'  => 'nullable|integer',
        ]);

        DB::beginTransaction();

        try {
            // 1️⃣ Create Buyer
            $buyer = Buyer::create([
                'company_id'      => 1,
                'business_name'    => $request->business_name,
                'category'         => $request->category,
                'email'            => $request->email,
                'phone'            => $request->phone,
                'tin'              => $request->tin,
                'trade_license_no' => $request->trade_license_no,
                'status'        => $request->status,
            ]);

            // 2️⃣ Create Contacts (if any)
            $contacts     = $request->input('contacts', []);
            $primaryIndex = $request->input('primary_contact_index');

            foreach ($contacts as $index => $contactData) {
                // Skip totally empty rows
                if (
                    empty($contactData['name']) &&
                    empty($contactData['email']) &&
                    empty($contactData['phone']) &&
                    empty($contactData['designation'])
                ) {
                    continue;
                }

                $buyer->contacts()->create([
                    'name'        => $contactData['name'] ?? null,
                    'email'       => $contactData['email'] ?? null,
                    'phone'       => $contactData['phone'] ?? null,
                    'designation' => $contactData['designation'] ?? null,
                    'is_primary'  => ((string)$primaryIndex === (string)$index),
                ]);
            }

            // Optional safety: if no primary is set but at least one contact exists, mark first as primary
            if (! $buyer->contacts()->where('is_primary', true)->exists()) {
                $first = $buyer->contacts()->first();
                if ($first) {
                    $first->update(['is_primary' => 1]);
                }
            }

            DB::commit();

            // Response for your AJAX
            return response()->json([
                'success' => true,
                'message' => 'Buyer created successfully.',
                'data'    => [
                    'id'   => $buyer->id,
                    'name' => $buyer->business_name,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the buyer.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($role, Buyer $buyer)
    {
        // Load contacts for edit
        $buyer->load('contacts');

        // For AJAX edit modal loader
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $buyer,
            ]);
        }

        // If you directly go to the edit page (optional)
        return view('admin.buyers.edit', [
            'buyer' => $buyer,
            'role'  => $role,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $role, string $id)
    {
        // try {
        DB::beginTransaction();
        $data = Buyer::findOrFail($id);
        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Data Info Not Found!'
            ]);
        }
        $data->business_name    = $request->business_name;
        $data->category         = $request->category;
        $data->email            = $request->email;
        $data->phone            = $request->phone;
        $data->tin              = $request->tin;
        $data->trade_license_no = $request->trade_license_no;
        $data->status         = $request->status;
        $data->save();

        $data->contacts()->delete();
        $buyerContact = $request->input('contacts', []);
        $primaryIndex = $request->input('primary_contact_index');
        foreach ($buyerContact as $index => $contactData) {
            // Skip totally empty rows
            if (
                empty($contactData['name']) &&
                empty($contactData['email']) &&
                empty($contactData['phone']) &&
                empty($contactData['designation'])
            ) {
                continue;
            }

            $data->contacts()->create([
                'name'        => $contactData['name'] ?? null,
                'email'       => $contactData['email'] ?? null,
                'phone'       => $contactData['phone'] ?? null,
                'designation' => $contactData['designation'] ?? null,
                'is_primary'  => ((string)$primaryIndex === (string)$index),
            ]);
        }

        // Optional safety: if no primary is set but at least one contact exists, mark first as primary
        if (! $data->contacts()->where('is_primary', true)->exists()) {
            $first = $data->contacts()->first();
            if ($first) {
                $first->update(['is_primary' => 1]);
            }
        }

        DB::commit();
        return response()->json([
            'success' => true,
            'message' => 'Data updated successfully.',
            'data' => $data
        ]);

        // } catch (\Throwable $th) {
        //     DB::rollBack();
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Something Went Wrong!',
        //         'error'   => $th->getMessage()
        //     ]);
        // }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($role, $id)
{

        try {
            $item = Buyer::findOrFail($id);
            if ($item) {
                $item->delete();
                $item->contacts()->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Data deleted successfully.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Info Not Found!'
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Something Went Wrong!',
                'error'   => $th->getMessage()
            ]);
    }
}
    public function getBuyerContacts($role, $id)
    {
        $buyer = Buyer::with('contacts')->find($id);
        if (!$buyer) {
            return response()->json([
                'success' => false,
                'message' => 'Buyer not found.'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $buyer->contacts
        ]);
    }
}
