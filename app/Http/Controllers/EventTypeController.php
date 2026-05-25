<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\EventType;

class EventTypeController extends Controller
{
    public function index(Request $request){
        $event_types = EventType::active()->when($request->search, function($query, $search){
            return $query->where('name', 'like', '%'.$search.'%');
        })->paginate(20);
        return view('event_types.index', compact('event_types'));
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

        EventType::create($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function edit(Request $request, EventType $event_type){
        return response()->json($event_type);
    }

    public function update(Request $request, EventType $event_type){
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        $event_type->update($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy(Request $request, EventType $event_type){
        $event_type->update([
            'deleted' => 1
        ]);

        return response()->json([
            'status' => true
        ]);
    }
}
