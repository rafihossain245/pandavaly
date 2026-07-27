<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Attendance;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Attendance::select('attendances.*')
            ->leftJoin('users', 'users.id', '=', 'attendances.user_id')
            ->leftJoin('companies', 'companies.id', '=', 'attendances.company_id')
            ->leftJoin('shifts', 'shifts.id', '=', 'attendances.shift_id')
            ->leftJoin('attendence_settings', 'attendence_settings.id', '=', 'attendances.attendence_setting_id')
            ->orderBy('users.name', 'asc')
            ->orderBy('attendances.id', 'asc');

        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('attendances.user_id', $request->user_id);
        }

        if ($request->has('company_id') && !empty($request->company_id)) {
            $query->where('attendances.company_id', $request->company_id);
        }

        if ($request->has('shift_id') && !empty($request->shift_id)) {
            $query->where('attendances.shift_id', $request->shift_id);
        }

        if ($request->filled('date')) {
            $query->where('attendances.date', $request->date);
        }

        $datas = $query->paginate(20);
        $users = User::orderBy('name')->where('is_super_admin', 0)->get();
        $companies = Company::orderBy('name')->get();
        $shifts = Shift::orderBy('name')->get();

        return view('attendances.index', compact(
            'datas',
            'users',
            'companies',
            'shifts'
        ));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('attendances.create-modal');
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
            'leave_type_id' => 'required',
            'company_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'required'
        ]);

        // If validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        if (Carbon::parse($request->end_date)->lt(Carbon::parse($request->start_date))) {
            return response()->json([
                'message' => 'End date cannot be earlier than start date.'
            ]);
        }

        $exists = Attendance::where('user_id', $request->user_id)
            // ->where('leave_type_id', $request->leave_type_id)
            ->where('status', 'pending')
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function ($q2) use ($request) {
                        $q2->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                    });
            })
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending leave of this type within the selected date range!'
            ]);
        }

        $data = Attendance::create([
            'user_id' => $request->user_id,
            'leave_type_id' => $request->leave_type_id,
            'company_id' => $request->company_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => $request->status
        ]);

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
        return view('attendances.edit-modal', compact('id'));
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
        $data = Attendance::findOrFail($id);
        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Data Info Not Found!'
            ]);
        }
        $validated = $request->validate([
            'user_id' => 'required',
            'leave_type_id' => 'required',
            'company_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'status' => 'required'
        ]);

        $data->update([
            'user_id' => $request->user_id,
            'leave_type_id' => $request->leave_type_id,
            'company_id' => $request->company_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
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
            $item = Attendance::find($request->item_id);
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
