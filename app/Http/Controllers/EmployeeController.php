<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\User;

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

        $data = $request->all();

        if ($request->has_user) {
            $userValidator = Validator::make($request->all(), [
                'username' => 'required|unique:users,user',
                'role' => 'required',
                'password' => 'required'
            ]);

            if($userValidator->fails()){
                return response()->json([
                    'status' => false,
                    'error' => 'Usuario: ' . $userValidator->errors()->first()
                ]);
            }

            $user = User::create([
                'name' => $request->name,
                'user' => $request->username,
                'role' => $request->role,
                'password' => bcrypt($request->password)
            ]);

            $data['user_id'] = $user->id;
        }

        Employee::create($data);

        return response()->json([
            'status' => true
        ]);
    }

    public function edit(Request $request, Employee $employee){
        $employee->load('user');
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

        $data = $request->all();

        if ($request->has_user) {
            $userValidator = Validator::make($request->all(), [
                'username' => 'required|unique:users,user,' . optional($employee->user)->id,
                'role' => 'required'
            ]);

            if($userValidator->fails()){
                return response()->json([
                    'status' => false,
                    'error' => 'Usuario: ' . $userValidator->errors()->first()
                ]);
            }

            if ($employee->user) {
                $employee->user->update([
                    'name' => $request->name,
                    'user' => $request->username,
                    'role' => $request->role,
                ]);
                if ($request->password) {
                    $employee->user->update([
                        'password' => bcrypt($request->password)
                    ]);
                }
            } else {
                if (!$request->password) {
                    return response()->json([
                        'status' => false,
                        'error' => 'Usuario: La contraseña es requerida'
                    ]);
                }
                $user = User::create([
                    'name' => $request->name,
                    'user' => $request->username,
                    'role' => $request->role,
                    'password' => bcrypt($request->password)
                ]);
                $data['user_id'] = $user->id;
            }
        }

        $employee->update($data);

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
