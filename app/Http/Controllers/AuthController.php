<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Inscription
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255', 
            'email' => 'required|email|unique:users,email', 
            'master_password' => 'required|min:8', 
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'master_password' => Hash::make($request->master_password),
        ]);

        return response()->json(['message' => 'Utilisateur créé avec succès.'], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email', 
            'master_password' => 'required', 
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->master_password, $user->master_password)) {
            return response()->json(['message' => 'Les informations d’identification sont incorrectes.'], 401);
        }
        return response()->json(['token' => $user->createToken('auth_token')->plainTextToken]);
    }
}
