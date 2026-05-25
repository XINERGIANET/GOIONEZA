<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Income;
use App\Models\IncomeType;
use App\Models\PaymentMethod;
use App\Models\Location;

class IncomeController extends Controller
{
    public function index(Request $request){

        $incomes = Income::active()->when($request->search, function($query, $search){
            return $query->where('description', 'like', '%'.$search.'%');
        })->paginate(10);

        $income_types = IncomeType::active()->get();
        $payment_methods = PaymentMethod::all();
        $locations = Location::active()->get();

        return view('incomes.index', compact('incomes', 'income_types', 'payment_methods', 'locations'));
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'description' => 'required',
            'amount' => 'required|numeric',
            'income_type_id' => 'required',
            'payment_method_id' => 'required',
            'location_id' => 'required',
            'date' => 'required|date',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        Income::create($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function edit(Request $request, Income $income){
        return response()->json([
            'id' => $income->id,
            'description' => $income->description,
            'amount' => $income->amount,
            'income_type_id' => $income->income_type_id,
            'payment_method_id' => $income->payment_method_id,
            'location_id' => $income->location_id,
            'date' => $income->date->format('Y-m-d')
        ]);
    }

    public function update(Request $request, Income $income){
        $validator = Validator::make($request->all(), [
            'description' => 'required',
            'amount' => 'required|numeric',
            'income_type_id' => 'required',
            'payment_method_id' => 'required',
            'location_id' => 'required',
            'date' => 'required|date'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        $income->update($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy(Request $request, Income $income){
        $income->update([
            'deleted' => 1
        ]);

        return response()->json([
            'status' => true
        ]);
    }
}
