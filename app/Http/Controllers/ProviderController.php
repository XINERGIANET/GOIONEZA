<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Provider;

class ProviderController extends Controller
{
    public function index(Request $request){
        $providers = Provider::active()->when($request->search, function($query, $search){
            return $query->where('name', 'like', '%'.$search.'%');
        })->paginate(10);
        return view('providers.index', compact('providers'));
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

        Provider::create($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function edit(Request $request, Provider $provider){
        return response()->json($provider);
    }

    public function update(Request $request, Provider $provider){
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

        $provider->update($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy(Request $request, Provider $provider){
        $provider->update([
            'deleted' => 1
        ]);

        return response()->json([
            'status' => true
        ]);
    }
}
