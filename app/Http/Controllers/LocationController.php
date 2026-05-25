<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Location;

class LocationController extends Controller
{
    public function index(Request $request){
        $locations = Location::active()->when($request->search, function($query, $search){
            return $query->where('name', 'like', '%'.$search.'%');
        })->paginate(20);
        return view('locations.index', compact('locations'));
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

        Location::create($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function edit(Request $request, Location $location){
        return response()->json($location);
    }

    public function update(Request $request, Location $location){
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        $location->update($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy(Request $request, Location $location){
        $location->update([
            'deleted' => 1
        ]);

        return response()->json([
            'status' => true
        ]);
    }
}
