<?php

namespace App\Http\Controllers;

use App\Models\EmployeeSalary;
use App\Models\SalaryTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeSalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = EmployeeSalary::select('employee_salaries.*')
            ->join('users', 'users.id', '=', 'employee_salaries.user_id')
            ->join('salary_templates', 'salary_templates.id', '=', 'employee_salaries.salary_template_id')
            ->orderBy('users.name', 'asc')
            ->orderBy('salary_templates.name', 'asc')
            ->orderBy('employee_salaries.id', 'asc');

        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('employee_salaries.user_id', $request->user_id);
        }

        if ($request->has('salary_template_id') && !empty($request->salary_template_id)) {
            $query->where('employee_salaries.salary_template_id', $request->salary_template_id);
        }

        if ($request->filled('date')) {
            [$year, $month] = explode('-', $request->date);
            $query->where('employee_salaries.year', (int) $year)
                ->where('employee_salaries.month', str_pad($month, 2, '0', STR_PAD_LEFT));
        }

        if ($request->filled('status')) {
            $query->whereDate('employee_salaries.status', $request->status);
        }

        $datas = $query->paginate(20);
        $users = User::orderBy('name')->where('is_super_admin', 0)->get();
        $templates = SalaryTemplate::orderBy('name')->get();

        return view('employee-salaries.index', compact(
            'datas',
            'users',
            'templates'
        ));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('employee-salaries.create-modal');
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
            'salary_template_id' => 'required',
            'date' => 'required'            
        ]);

        // If validation fails
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ]);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }
        [$year, $month] = explode('-', $request->date);        
        $data = EmployeeSalary::updateOrCreate(
            [
                'user_id' => $request->user_id,
                'salary_template_id' => $request->salary_template_id,
            ],
            [                                
                'month' => (string) $month,
                'year' => (int) $year,
                'gross_salary' => $request->gross_salary,
                'total_deductions' => $request->total_deductions,
                'net_salary' => $request->net_salary,
                'payment_date' => $request->payment_date,
                'status' => $request->status
            ]
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data created successfully.',
                'data' => $data
            ]);
        }

        return redirect()->route('role.employee-salaries.index')->with('success', 'Data created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('employee-salaries.edit-modal', compact('id'));
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
        $data = EmployeeSalary::findOrFail($id);
        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Data Info Not Found!'
            ]);
        }
        $validated = $request->validate([
            'user_id' => 'required',
            'salary_template_id' => 'required',
            'date' => 'required'
        ]);

        [$year, $month] = explode('-', $request->date);
        $data->update([
            'user_id' => $request->user_id,
            'salary_template_id' => $request->salary_template_id,
            'month' => (string) $month,
            'year' => (int) $year,
            'gross_salary' => $request->gross_salary,
            'total_deductions' => $request->total_deductions,
            'net_salary' => $request->net_salary,
            'payment_date' => $request->payment_date,
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
            $item = EmployeeSalary::find($request->item_id);
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

    public function getEmployeeSalary(Request $request)
    {
        try {
            $data = EmployeeSalary::where('user_id', $request->user_id)->orderBy('id', 'ASC')->get();
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }

        return response()->json([
            'data' => $data,
            'success' => true,
            'message' => 'Data Found Successfully.'
        ]);
    }
}
