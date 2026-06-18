<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $commissions = Commission::when($request->search, function($query, $search){
            return $query->where('dni', 'like', '%'.$search.'%')
                         ->orWhere('names', 'like', '%'.$search.'%');
        })->paginate(20);
        return view('commissions.index', compact('commissions'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dni' => 'required',
            'names' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        Commission::create($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function show(Commission $commission)
    {
        //
    }

    public function edit(Commission $commission)
    {
        return response()->json($commission);
    }

    public function update(Request $request, Commission $commission)
    {
        $validator = Validator::make($request->all(), [
            'dni' => 'required',
            'names' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        $commission->update($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy(Commission $commission)
    {
        $commission->delete();
        
        return response()->json([
            'status' => true
        ]);
    }
}
