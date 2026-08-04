<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    public function index(Request $request){
        $payment_methods = PaymentMethod::active()->when($request->search, function($query, $search){
            return $query->where('name', 'like', '%'.$search.'%');
        })->paginate(20);

        return view('payment_methods.index', compact('payment_methods'));
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

        PaymentMethod::create($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function edit(Request $request, PaymentMethod $payment_method){
        return response()->json($payment_method);
    }

    public function update(Request $request, PaymentMethod $payment_method){
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        $payment_method->update($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy(Request $request, PaymentMethod $payment_method){
        if (strpos($payment_method->name, '(Inactivo)') === false) {
            $payment_method->update([
                'name' => $payment_method->name . ' (Inactivo)'
            ]);
        }

        return response()->json([
            'status' => true
        ]);
    }
}
