<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sublocation;
use Illuminate\Support\Facades\Validator;

class SublocationController extends Controller
{
    public function index(Request $request)
    {
        $sublocations = Sublocation::active()->where('location_id', $request->location_id)->get();
        return response()->json($sublocations);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'location_id' => 'required|exists:locations,id'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        Sublocation::create($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy(Request $request, Sublocation $sublocation)
    {
        $sublocation->update([
            'deleted' => 1
        ]);

        return response()->json([
            'status' => true
        ]);
    }
}
