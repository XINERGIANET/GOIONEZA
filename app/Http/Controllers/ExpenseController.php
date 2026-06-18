<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Expense;
use App\Models\Contract;
use App\Models\PaymentMethod;

class ExpenseController extends Controller
{
    public function index(Request $request){
        $expenses = Expense::active()->when($request->description, function($query, $description){
            return $query->where('description', 'like', '%'.$description.'%');
        })->when($request->start_date, function($query, $start_date){
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date, function($query, $end_date){
            return $query->whereDate('date', '<=', $end_date);
        })->latest('date');

        $total = $expenses->sum('amount');
        $expenses = $expenses->paginate(20);

        $contracts = Contract::active()->get();
        $payment_methods = PaymentMethod::where('name', 'not like', '%(Inactivo)%')->get();
        return view('expenses.index', compact('expenses', 'contracts', 'payment_methods', 'total'));
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'contract_id' => 'required',
            'description' => 'required',
            'responsible' => 'required',
            'voucher' => 'required',
            'voucher_number' => 'required',
            'provider' => 'required',
            'amount' => 'required|numeric',
            'payment_method_id' => 'required',
            'date' => 'required|date'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        Expense::create($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function edit(Request $request, Expense $expense){
        return response()->json([
            'id' => $expense->id,
            'contract_id' => $expense->contract_id,
            'description' => $expense->description,
            'responsible' => $expense->responsible,
            'voucher' => $expense->voucher,
            'voucher_number' => $expense->voucher_number,
            'provider' => $expense->provider,
            'amount' => $expense->amount,
            'payment_method_id' => $expense->payment_method_id,
            'date' => $expense->date->format('Y-m-d'),
        ]);
    }

    public function update(Request $request, Expense $expense){
        $validator = Validator::make($request->all(), [
            'contract_id' => 'required',
            'description' => 'required',
            'responsible' => 'required',
            'voucher' => 'required',
            'voucher_number' => 'required',
            'provider' => 'required',
            'amount' => 'required|numeric',
            'payment_method_id' => 'required',
            'date' => 'required|date'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        $expense->update($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy(Request $request, Expense $expense){
        $expense->update([
            'deleted' => 1
        ]);

        return response()->json([
            'status' => true
        ]);
    }

    public function report(Request $request){

        $contracts = Expense::select('contract_id')->when($request->start_date, function($query, $start_date){
            return $query->whereDate('date', '>=', $start_date);
            })->when($request->end_date, function($query, $end_date){
            return $query->whereDate('date', '<=', $end_date);
            })->groupBy('contract_id')->get();


        return view('expenses.report', compact('contracts'));
    }
}
