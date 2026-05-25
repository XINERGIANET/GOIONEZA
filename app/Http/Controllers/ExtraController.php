<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Extra;

class ExtraController extends Controller
{
    public function index(Request $request){
        $extras = Extra::active()->when($request->search, function($query, $search){
            return $query->where('name', 'like', '%'.$search.'%');
        })->paginate(20);
        return view('extras.index', compact('extras'));
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        Extra::create($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function edit(Request $request, Extra $extra){
        return response()->json($extra);
    }

    public function update(Request $request, Extra $extra){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required|numeric'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        $extra->update($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy(Request $request, Extra $extra){
        $extra->update([
            'deleted' => 1
        ]);

        return response()->json([
            'status' => true
        ]);
    }
}
