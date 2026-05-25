<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Package;

class PackageController extends Controller
{
    public function index(Request $request){
        $packages = Package::active()->when($request->search, function($query, $search){
            return $query->where('name', 'like', '%'.$search.'%');
        })->paginate(20);
        return view('packages.index', compact('packages'));
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

        Package::create($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function edit(Request $request, Package $package){
        return response()->json($package);
    }

    public function update(Request $request, Package $package){
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

        $package->update($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy(Request $request, Package $package){
        $package->update([
            'deleted' => 1
        ]);

        return response()->json([
            'status' => true
        ]);
    }
}
