<?php

namespace App\Http\Controllers;

use App\Models\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PasswordController extends Controller
{
    public function index()
    {
        if (Auth::guest()) {
            return response()->json(['message' => 'Vous devez etre connecte pour acceder a cet url'], 401); 
        }
        return Auth::user()->passwords()->get();
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Vous devez etre connecte pour acceder a cet url'], 401);
        }

        $data = $request->validate([
            'encrypted_password' => 'required|string',
            'website' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'iv' => 'required|string'
        ]);

        return Auth::user()->passwords()->create($data);
    }

    public function show(Password $password)
    {
        if ($password->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return $password;
    }

    public function update(Request $request, Password $password)
    {
        if ($password->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'encrypted_password' => 'sometimes|string',
            'name' => 'sometimes|string|max:255',
        ], [
            'encrypted_password.required' => 'le mot de passe est requis.',
            'name.max' => 'le nom ne doit pas depasser 255 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $password->update($validator->validated());

        $data = $request->validate([
            'encrypted_password' => 'sometimes|string',
            'website' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:255',
            'iv' => 'sometimes|string'
        ]);

        $password->update($data);

        return $password;
    }

    public function destroy(Password $password)
    {
        if ($password->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $password->delete();
        return response()->noContent();
    }
}