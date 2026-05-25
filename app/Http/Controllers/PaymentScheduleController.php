<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentSchedule;

class PaymentScheduleController extends Controller
{
    public function index(Request $request){
        $payment_schedules = PaymentSchedule::active()->when($request->search, function($query, $search){
            return $query->where('description', 'like', '%'.$search.'%');
        })->paginate(20);
        return view('payment_schedules.index', compact('payment_schedules'));
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'description' => 'required',
            'amount' => 'required|numeric',
            'day' => 'required|integer|between:1,31'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        PaymentSchedule::create($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function edit(Request $request, PaymentSchedule $payment_schedule){
        return response()->json($payment_schedule);
    }

    public function update(Request $request, PaymentSchedule $payment_schedule){
        $validator = Validator::make($request->all(), [
            'description' => 'required',
            'amount' => 'required|numeric',
            'day' => 'required|integer|between:1,31'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        $payment_schedule->update($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy(Request $request, PaymentSchedule $payment_schedule){
        $payment_schedule->update([
            'deleted' => 1
        ]);

        return response()->json([
            'status' => true
        ]);
    }
}
