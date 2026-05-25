<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\ProductType;

class ProductTypeController extends Controller
{
    public function index(Request $request){
        $product_types = ProductType::active()->when($request->search, function($query, $search){
            return $query->where('name', 'like', '%'.$search.'%');
        })->paginate(20);
        return view('product_types.index', compact('product_types'));
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

        ProductType::create($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function edit(Request $request, ProductType $product_type){
        return response()->json($product_type);
    }

    public function update(Request $request, ProductType $product_type){
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        $product_type->update($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy(Request $request, ProductType $product_type){
        $product_type->update([
            'deleted' => 1
        ]);

        return response()->json([
            'status' => true
        ]);
    }
}
