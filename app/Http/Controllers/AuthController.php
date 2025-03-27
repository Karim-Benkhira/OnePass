<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use App\Mail\LimitingMail;
use App\Mail\NewDeviceNotification;
use Illuminate\Support\Facades\RateLimiter;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

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

        // Capture device information during registration
        $agent = new Agent();
        $deviceName = $agent->device() ?: 'Unknown Device';
        $deviceType = $agent->isMobile() ? 'Mobile' : 'Desktop';
        $ipAddress = $request->ip();
        $userAgentData = $request->header('User-Agent');

        // Store the device as verified since it's the registration device
        Device::create([
            'user_id' => $user->id,
            'device_name' => $deviceName,
            'device_type' => $deviceType,
            'ip_address' => $ipAddress,
            'user_agent_data' => $userAgentData,
            'last_login' => now(),
            'is_verified' => true,
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

        $key = $request->email; 
        RateLimiter::hit($key, 60);

        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->sendLimitingMail($request->email);
            return response()->json(['message' => 'Trop de tentatives. Verifiez votre e-mail.','key'=>$key], 429);
        }

        if (!$user || !Hash::check($request->master_password, $user->master_password)) {
            return response()->json(['message' => 'Les informations d identification sont incorrectes.'], 401);
        }

        // Get device details
        $agent = new Agent();
        $deviceName = $agent->device() ?: 'Unknown Device';
        $deviceType = $agent->isMobile() ? 'Mobile' : 'Desktop';
        $ipAddress = $request->ip();
        $userAgentData = $request->header('User-Agent');

        // Check if device is verified
        $existingDevice = Device::where('user_id', $user->id)
            ->where('ip_address', $ipAddress)
            ->where('device_name', $deviceName)
            ->where('device_type', $deviceType)
            ->first();

        if ($existingDevice && $existingDevice->is_verified) {
            $existingDevice->update(['last_login' => now()]);
            return response()->json(['token' => $user->createToken('auth_token')->plainTextToken]);
        } 
        elseif ($existingDevice && !$existingDevice->is_verified) {
            $verificationUrl = $this->generateVerificationUrl($user, $existingDevice, $deviceName, $deviceType, $ipAddress);
            $this->sendVerificationEmail($user, $verificationUrl, $deviceName);
            
            return response()->json([
                'message' => 'Device needs verification. Check your email.',
                'token' => $user->createToken('auth_token', [])->plainTextToken
            ], 403);
        }
        else {
            // New device - create record and send verification
            $newDevice = Device::create([
                'user_id' => $user->id,
                'device_name' => $deviceName,
                'device_type' => $deviceType,
                'ip_address' => $ipAddress,
                'user_agent_data' => $userAgentData,
                'last_login' => now(),
                'is_verified' => false,
            ]);

            $verificationUrl = $this->generateVerificationUrl($user, $newDevice, $deviceName, $deviceType, $ipAddress);
            $this->sendVerificationEmail($user, $verificationUrl, $deviceName);

            return response()->json([
                'message' => 'New device detected. Verification email sent.',
                'token' => $user->createToken('auth_token', [])->plainTextToken
            ], 403);
        }
    }

    private function generateVerificationUrl($user, $device, $deviceName, $deviceType, $ipAddress)
    {
        return URL::temporarySignedRoute(
            'device.verify',
            now()->addHours(24),
            [
                'device_id' => $device->id,
                'user_id' => $user->id,
                'device_name' => $deviceName,
                'device_type' => $deviceType,
                'ip_address' => $ipAddress,
            ]
        );
    }

    private function sendVerificationEmail($user, $verificationUrl, $deviceName)
    {
        try {
            Mail::to($user->email)->send(new NewDeviceNotification($user, $verificationUrl));
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send verification email: " . $e->getMessage());
            return false;
        }
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
