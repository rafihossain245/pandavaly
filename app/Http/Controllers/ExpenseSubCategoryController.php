<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ExpenseCategory;
use App\Models\ExpenseSubCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpenseSubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = ExpenseSubCategory::select('expense_sub_categories.*')
            ->leftJoin('users', 'users.id', '=', 'expense_sub_categories.user_id')
            ->leftJoin('companies', 'companies.id', '=', 'expense_sub_categories.company_id')
            ->leftJoin('expense_categories', 'expense_categories.id', '=', 'expense_sub_categories.expense_category_id')
            ->orderBy('users.name', 'asc')
            ->orderBy('expense_categories.name', 'asc')
            ->orderBy('expense_sub_categories.name', 'asc');

        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('expense_sub_categories.user_id', $request->user_id);
        }

        if ($request->has('company_id') && !empty($request->company_id)) {
            $query->where('expense_sub_categories.company_id', $request->company_id);
        }

        if ($request->has('expense_category_id') && !empty($request->expense_category_id)) {
            $query->where('expense_sub_categories.expense_category_id', $request->expense_category_id);
        }

        if ($request->filled('name')) {
            $query->where('expense_sub_categories.name', $request->name);
        }

        if ($request->filled('status')) {
            $query->whereDate('expense_sub_categories.status', $request->status);
        }

        $datas = $query->paginate(20);                
        $users = User::orderBy('name')->where('is_super_admin', 0)->get();
        $companies = Company::orderBy('name')->get();
        $expense_categories = ExpenseCategory::orderBy('name')->where('status', 1)->get();

        return view('expense-subcategories.index', compact(
            'datas',
            'users',
            'companies',
            'expense_categories'
        ));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('expense-subcategories.create-modal');
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
            'name' => 'required',
            'expense_category_id' => 'required'          
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
  
        $data = ExpenseSubCategory::create([
            'user_id' => $request->user_id,    
            'company_id' => $request->company_id,    
            'expense_category_id' => $request->expense_category_id,    
            'name' => $request->name,    
            'description' => $request->description,
            'status' => $request->status ? 1 : 0
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data created successfully.',
                'data' => $data
            ]);
        }

        return redirect()->route('role.expense-subcategories.index')->with('success', 'Data created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('expense-subcategories.edit-modal', compact('id'));
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
        $data = ExpenseSubCategory::findOrFail($id);
        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Data Info Not Found!'
            ]);
        }
        $validated = $request->validate([
            'name' => 'required',
            'expense_category_id' => 'required'
        ]);

        $data->update([
            'user_id' => $request->user_id,
            'company_id' => $request->company_id,
            'expense_category_id' => $request->expense_category_id,
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status ? 1 : 0
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
            $item = ExpenseSubCategory::find($request->item_id);
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

    public function getExpenseSubCategory(Request $request)
    {
        try {
            $data = ExpenseSubCategory::where('expense_category_id', $request->expense_category_id)->orderBy('id', 'ASC')->get();
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
