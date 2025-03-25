<?php

namespace App\Http\Controllers;

use App\Models\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PasswordController extends Controller
{
    public function index()
    {
        return Auth::user()->passwords()->get();
    }

    public function store(Request $request)
    {
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