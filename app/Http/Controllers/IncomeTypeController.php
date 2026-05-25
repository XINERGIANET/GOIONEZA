<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\IncomeType;

class IncomeTypeController extends Controller
{
    public function index(Request $request){
        $income_types = IncomeType::active()->when($request->search, function($query, $search){
            return $query->where('name', 'like', '%'.$search.'%');
        })->paginate(20);
        return view('income_types.index', compact('income_types'));
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

        IncomeType::create($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function edit(Request $request, IncomeType $income_type){
        return response()->json($income_type);
    }

    public function update(Request $request, IncomeType $income_type){
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        $income_type->update($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy(Request $request, IncomeType $income_type){
        $income_type->update([
            'deleted' => 1
        ]);

        return response()->json([
            'status' => true
        ]);
    }
}
