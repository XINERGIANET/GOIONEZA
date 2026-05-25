<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index(){
        $pin = DB::table('settings')->pluck('pin')->first();
        
        return view('settings', compact('pin'));
    }

    public function password(Request $request){
        $request->validate([
            'password' => 'required',
            'new_password' => 'required|min:5'
        ]);

        if(!Hash::check($request->password, auth()->user()->password)){
            return back()->withErrors([
                'password' => 'La contraseña actual no coincide'
            ]);
        }

        auth()->user()->update([
            'password' => Hash::make($request->new_password)
        ]);

        session()->flash('message', 'Contraseña actualizada');

        return redirect()->route('settings.index');
    }

    public function pin(Request $request){
        $request->validate([
            'pin' => 'required|size:4',
        ]);

        DB::table('settings')->update([
            'pin' => $request->pin
        ]);

        session()->flash('message', 'PIN actualizado');

        return redirect()->route('settings.index');
    }
}
