<?php

namespace App\Http\Controllers;

use App\Models\AdvanceSalary;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdvanceSalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = AdvanceSalary::select('advance_salaries.*')
            ->join('users', 'users.id', '=', 'advance_salaries.user_id')
            ->orderBy('users.name', 'asc')
            ->orderBy('advance_salaries.id', 'asc');

        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('advance_salaries.user_id', $request->user_id);
        }

        if ($request->filled('month')) {
            $query->where('advance_salaries.month', $request->month);
        }

        if ($request->filled('status')) {
            $query->whereDate('advance_salaries.status', $request->status);
        }
        
        $datas = $query->paginate(20);
        $users = User::orderBy('name')->where('is_super_admin', 0)->get();

        return view('advance-salaries.index', compact(
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
        return view('advance-salaries.create-modal');
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
            'month' => 'required'
        ]);

        // If validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        try {
            $data = AdvanceSalary::create([
                'user_id' => $request->user_id,
                'amount' => $request->amount,                
                'month' => $request->month,                                
                'reason' => $request->reason,                                
                'status' => $request->status                                                                
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
        return view('advance-salaries.edit-modal', compact('id'));
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
        $data = AdvanceSalary::findOrFail($id);
        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Data Info Not Found!'
            ]);
        }
        $validated = $request->validate([
            'user_id' => 'required',
            'amount' => 'required',
            'month' => 'required'
        ]);

        $data->update([
            'user_id' => $request->user_id,
            'amount' => $request->amount,
            'month' => $request->month,
            'reason' => $request->reason,
            'status' => $request->status
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
            $item = AdvanceSalary::find($request->item_id);
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
