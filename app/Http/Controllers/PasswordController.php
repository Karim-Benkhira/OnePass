<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Password;
use Illuminate\Support\Facades\Auth;

class PasswordController extends Controller
{

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Utilisateur non connecte'], 401);
        }

        $request->validate(['encrypted_password' => 'required']);
        $password = Password::create([
            'user_id' => Auth::id(),
            'encrypted_password' => $request->encrypted_password,
        ]);

        return response()->json($password, 201);
    }

    public function index()
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Utilisateur non connecte'], 401);
        }

        return response()->json(Password::where('user_id', Auth::id())->get());
    }    

    public function update(Request $request, Password $password)
    {
        if ($password->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate(['encrypted_password' => 'required']);
        $password->update(['encrypted_password' => $request->encrypted_password]);

        return response()->json($password);
    }

    public function destroy(Password $password)
    {
        if ($password->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $password->delete();
        return response()->json(['message' => 'Mot de passe supprime']);
    }
}
