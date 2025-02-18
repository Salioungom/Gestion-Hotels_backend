<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function register(Request $request){
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = new \App\Models\User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        // dd($user);
        $user->save();

        return response()->json($user,
    '201');
    }
Public function login(Request $request){
    if(auth()->attempt($request->only(['email','password'])))
    {
        $user=auth()->user();
        $token=$user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'status_code' => 200,
            'status_message' => 'User connecté en tant que'.' '.$user->role,
            'Utilisateur'=>$user,
            'token'=>$token,
        ]);
    }else{
        return response()->json([
            'status_code' => 403,
            'status_message' => 'Infromation d\'authentification incorrecte',

        ]);
    } 
   
    
}
public function logout(Request $request){
    auth()->user()->tokens()->delete();
    return response()->json([
        'status_code' => 200,
        'status_message' => 'Utilisateur déconnecté',
    ]);
}
}

