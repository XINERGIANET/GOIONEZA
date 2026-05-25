<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Client;

class ClientController extends Controller
{
    public function index(Request $request){
        $clients = Client::active()->when($request->search, function($query, $search){
            return $query->where('name', 'like', '%'.$search.'%');
        })->paginate(10);
        return view('clients.index', compact('clients'));
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

        Client::create($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function edit(Request $request, Client $client){
        return response()->json($client);
    }

    public function update(Request $request, Client $client){
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

        $client->update($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy(Request $request, Client $client){
        $client->update([
            'deleted' => 1
        ]);

        return response()->json([
            'status' => true
        ]);
    }
}
