<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Purchase;
use App\Models\ExpenseType;
use App\Models\PaymentMethod;

class PurchaseController extends Controller
{
    public function index(Request $request){
        
        $purchases = Purchase::active()->when($request->description, function($query, $description){
            return $query->where('description', 'like', '%'.$description.'%');
        })->when($request->start_date, function($query, $start_date){
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date, function($query, $end_date){
            return $query->whereDate('date', '<=', $end_date);
        });

        $total = $purchases->sum('amount');

        $purchases = $purchases->paginate(20);

        $expense_types = ExpenseType::active()->get();
        
        $payment_methods = PaymentMethod::all();

        return view('purchases.index', compact('purchases', 'expense_types', 'payment_methods', 'total'));
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'description' => 'required',
            'voucher' => 'required',
            'voucher_number' => 'required',
            'provider' => 'required',
            'amount' => 'required|numeric',
            'expense_type_id' => 'required',
            'payment_method_id' => 'required',
            'date' => 'required|date',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        Purchase::create($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function edit(Request $request, Purchase $purchase){
        return response()->json([
            'id' => $purchase->id,
            'description' => $purchase->description,
            'voucher' => $purchase->voucher,
            'voucher_number' => $purchase->voucher_number,
            'provider' => $purchase->provider,
            'amount' => $purchase->amount,
            'expense_type_id' => $purchase->expense_type_id,
            'payment_method_id' => $purchase->payment_method_id,
            'date' => $purchase->date->format('Y-m-d')
        ]);
    }

    public function update(Request $request, Purchase $purchase){
        $validator = Validator::make($request->all(), [
            'description' => 'required',
            'voucher' => 'required',
            'voucher_number' => 'required',
            'provider' => 'required',
            'amount' => 'required|numeric',
            'expense_type_id' => 'required',
            'payment_method_id' => 'required',
            'date' => 'required|date',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        $purchase->update($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy(Request $request, Purchase $purchase){
        $purchase->update([
            'deleted' => 1
        ]);

        return response()->json([
            'status' => true
        ]);
    }

    public function report(Request $request){
        $providers = DB::table('purchases')->select('provider')->when($request->start_date, function($query, $start_date){
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date, function($query, $end_date){
            return $query->whereDate('date', '<=', $end_date);
        })->groupBy('provider')->orderBy('provider', 'asc')->get();

        return view('purchases.report', compact('providers'));
    }
}
