<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\LimitingMail;
use Illuminate\Support\Facades\RateLimiter;


class AuthController extends Controller
{
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

        return response()->json(['message' => 'Utilisateur cree avec succes'], 201);
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

        $key =$request->email; 
        RateLimiter::hit($key, 60);

        if (RateLimiter::tooManyAttempts($key, 10)) {
        $this->sendLimitingMail($request->email);
        return response()->json(['message' => 'Trop de tentatives. Verifiez votre e-mail.','key'=>$key], 429);
         }

        if (!$user || !Hash::check($request->master_password, $user->master_password)) {
            return response()->json(['message' => 'Les informations d identification sont incorrectes.'], 401);
        }
        return response()->json(['token' => $user->createToken('auth_token')->plainTextToken]);
    }



    public function sendLimitingMail($userEmail)
    {
        if (!$userEmail) {
            return response()->json(['error' => 'Aucun e-mail fourni'], 400);
        }
    
        Mail::to($userEmail)->send(new LimitingMail());
    
        return response()->json(['message' => 'E-mail de mise en garde envoye avec succes']);
    }
    

}
