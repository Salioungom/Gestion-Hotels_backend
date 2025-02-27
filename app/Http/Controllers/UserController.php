<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

    
use Illuminate\Support\Facades\Log;
class UserController extends Controller
{
public function register(Request $request) {
    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:4',
    ]);

    Log::info("Données reçues pour inscription :", $request->all()); // Log des données reçues

    $user = new \App\Models\User;
    $user->name = $request->name;
    $user->email = $request->email;
    $user->password = bcrypt($request->password);

    $user->save();

    return response()->json([
        'message' => 'Utilisateur enregistré avec succès',
        'user' => $user
    ], 201);
} 

public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status_code' => 200,
            'status_message' => 'Connexion réussie',
            'user' => $user,
            'token' => $token
        ]);
    }

    return response()->json([
        'status_code' => 403,
        'message' => 'Identifiants invalides'
    ], 403);
}

public function logout(Request $request){
    $request->user()->tokens()->delete();
    return response()->json([
        'status_code' => 200,
        'status_message' => 'Utilisateur déconnecté',
    ]);
}

}

