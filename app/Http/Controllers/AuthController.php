<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'master_password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'master_password' => Hash::make($request->master_password),
        ]);

        return response()->json(['message' => 'Utilisateur cree'], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'master_password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->master_password, $user->master_password)) {
            return response()->json(['email' => ['Les informations d’identification sont incorrectes.']], 401);
        }

        return response()->json(['token' => $user->createToken('auth_token')->plainTextToken]);
    }

}

