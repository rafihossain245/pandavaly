<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Loan::select('loans.*')
            ->join('users', 'users.id', '=', 'loans.user_id')
            ->orderBy('users.name', 'asc')
            ->orderBy('loans.id', 'asc');

        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('loans.user_id', $request->user_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('loans.start_date', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('loans.end_date', $request->end_date);
        }

        $datas = $query->paginate(20);
        $users = User::orderBy('name')->where('is_super_admin', 0)->get();

        return view('loans.index', compact(
            'datas',
            'users'
        ));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('loans.create-modal');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'amount' => 'required',
            'start_date' => 'required'
        ]);

        // If validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);            
        }

        try {            
            $data = Loan::create([
                'user_id' => $request->user_id,            
                'amount' => $request->amount,
                'remaining_amount' => $request->remaining_amount ?? 0,
                'monthly_deduction' => $request->monthly_deduction ?? 0,
                'status' => $request->status ?? 'Running',
                'start_date' => $request->start_date,
                'end_date' => $request->end_date
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data created successfully.',
            'data' => $data
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('loans.edit-modal', compact('id'));
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
        $id = $request->id;
        $data = Loan::findOrFail($id);
        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Data Info Not Found!'
            ]);
        }
        $validated = $request->validate([
            'user_id' => 'required',
            'amount' => 'required',
            'start_date' => 'required'
        ]);

        $data->update([
            'user_id' => $request->user_id,
            'amount' => $request->amount,
            'remaining_amount' => $request->remaining_amount ?? 0,
            'monthly_deduction' => $request->monthly_deduction ?? 0,
            'status' => $request->status ?? 'Running',
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data updated successfully.',
                'data' => $data
            ]);
        }

        return redirect('/super-admin/airport')->with('success', 'Data updated successfully.');
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
            $item = Loan::find($request->item_id);
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
}
