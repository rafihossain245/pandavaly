<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseSubCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $req_subdatas = [];
        $query = Expense::select('expenses.*')
            ->leftJoin('users', 'users.id', '=', 'expenses.user_id')
            ->leftJoin('companies', 'companies.id', '=', 'expenses.company_id')
            ->leftJoin('banks', 'banks.id', '=', 'expenses.bank_id')
            ->leftJoin('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
            ->leftJoin('expense_sub_categories', 'expense_sub_categories.id', '=', 'expenses.expense_sub_category_id')
            ->orderBy('users.name', 'asc')
            ->orderBy('expense_categories.name', 'asc')
            ->orderBy('expenses.title', 'asc');

        if ($request->has('user_id') && !empty($request->user_id)) {
            $query->where('expenses.user_id', $request->user_id);
        }

        if ($request->has('company_id') && !empty($request->company_id)) {
            $query->where('expenses.company_id', $request->company_id);
        }

        if ($request->has('bank_id') && !empty($request->bank_id)) {
            $query->where('expenses.bank_id', $request->bank_id);
        }

        if ($request->has('expense_category_id') && !empty($request->expense_category_id)) {
            $query->where('expenses.expense_category_id', $request->expense_category_id);
            $req_subdatas = ExpenseSubCategory::where('expense_category_id', $request->expense_category_id)->get();
        }

        if ($request->has('expense_sub_category_id') && !empty($request->expense_sub_category_id)) {
            $query->where('expenses.expense_sub_category_id', $request->expense_sub_category_id);
        }

        if ($request->filled('title')) {
            $query->where('expenses.title', $request->title);
        }

        if ($request->filled('status')) {
            $query->whereDate('expenses.status', $request->status);
        }

        $datas = $query->paginate(20);                
        $users = User::orderBy('name')->where('is_super_admin', 0)->get();
        $companies = Company::orderBy('name')->get();
        $expense_categories = ExpenseCategory::orderBy('name')->where('status', 1)->get();
        $banks = Bank::orderBy('name')->where('status', 1)->get();

        return view('expenses.index', compact(
            'datas',
            'users',
            'banks',
            'companies',
            'req_subdatas',
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
        return view('expenses.create-modal');
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
            'title' => 'required',
            'amount' => 'required',
            'expense_date' => 'required',
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
  
        $data = Expense::create([
            'user_id' => $request->user_id,
            'company_id' => $request->company_id,
            'expense_category_id' => $request->expense_category_id,
            'expense_sub_category_id' => $request->expense_sub_category_id,
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
            'payment_mode' => $request->payment_mode,
            'bank_id' => $request->bank_id,
            'reference' => $request->reference,
            'expense_date' => $request->expense_date,
            'status' => $request->status ? 1 : 0
        ]);


        $attachment = $request->file('attachment');
        if ($attachment) {
            $attachment_name = uniqid() . '.' . strtolower($attachment->getClientOriginalExtension());
            $upload_path = 'image/expense/';
            if (!file_exists(public_path($upload_path))) {
                mkdir(public_path($upload_path), 0777, true);
            }
            $success = $attachment->move(public_path($upload_path), $attachment_name);
            if ($success) {
                if (!empty($data->attachment) && file_exists(public_path($data->attachment))) {
                    unlink(public_path($data->attachment));
                }
                $data->attachment = $upload_path . $attachment_name;
            }
        }
        $data->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data created successfully.',
                'data' => $data
            ]);
        }

        return redirect()->route('role.expenses.index')->with('success', 'Data created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('expenses.edit-modal', compact('id'));
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
        $data = Expense::findOrFail($id);
        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Data Info Not Found!'
            ]);
        }
        $validated = $request->validate([
            'title' => 'required',
            'amount' => 'required',
            'expense_date' => 'required',
            'expense_category_id' => 'required'
        ]);

        // Handle attachment upload
        if ($request->hasFile('attachment')) {
            $upload_path = 'image/expense/';
            if (!file_exists(public_path($upload_path))) {
                mkdir(public_path($upload_path), 0777, true);
            }

            // Delete old attachment
            if (!empty($data->attachment) && file_exists(public_path($data->attachment))) {
                unlink(public_path($data->attachment));
            }

            $file = $request->file('attachment');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path($upload_path), $filename);
            $data->attachment = $upload_path . $filename;
        }

        $data->update([
            'user_id' => $request->user_id,
            'company_id' => $request->company_id,
            'expense_category_id' => $request->expense_category_id,
            'expense_sub_category_id' => $request->expense_sub_category_id,
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
            'payment_mode' => $request->payment_mode,
            'bank_id' => $request->bank_id,
            'reference' => $request->reference,
            'expense_date' => $request->expense_date,
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
            $item = Expense::find($request->item_id);
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
