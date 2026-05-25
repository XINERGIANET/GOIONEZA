<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\ExpenseType;

class ExpenseTypeController extends Controller
{
    public function index(Request $request){
        $expense_types = ExpenseType::active()->when($request->search, function($query, $search){
            return $query->where('name', 'like', '%'.$search.'%');
        })->paginate(20);
        return view('expense_types.index', compact('expense_types'));
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        ExpenseType::create($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function edit(Request $request, ExpenseType $expense_type){
        return response()->json($expense_type);
    }

    public function update(Request $request, ExpenseType $expense_type){
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        $expense_type->update($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy(Request $request, ExpenseType $expense_type){
        $expense_type->update([
            'deleted' => 1
        ]);

        return response()->json([
            'status' => true
        ]);
    }
}
