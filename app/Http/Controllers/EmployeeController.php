<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;

class EmployeeController extends Controller
{
    public function index(Request $request){
        $employees = Employee::active()->when($request->search, function($query, $search){
            return $query->where('name', 'like', '%'.$search.'%');
        })->paginate(10);
        return view('employees.index', compact('employees'));
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'document' => 'required',
            'name' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        Employee::create($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function edit(Request $request, Employee $employee){
        return response()->json($employee);
    }

    public function update(Request $request, Employee $employee){
        $validator = Validator::make($request->all(), [
            'document' => 'required',
            'name' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        $employee->update($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy(Request $request, Employee $employee){
        $employee->update([
            'deleted' => 1
        ]);

        return response()->json([
            'status' => true
        ]);
    }
}
