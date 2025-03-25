<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Jenssegers\Agent\Agent;
use App\Mail\NewDeviceNotification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'master_password' => 'required|string|min:8',
        ]);

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

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Login user and check device verification status
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'master_password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->master_password, $user->master_password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Get device details
        $agent = new Agent();
        $deviceName = $agent->device() ?: 'Unknown Device';
        $deviceType = $agent->isMobile() ? 'Mobile' : 'Desktop';
        $ipAddress = $request->ip();
        $userAgentData = $request->header('User-Agent');

        Log::info("Login attempt from device: $deviceName, IP: $ipAddress");

        // Check if this device is already registered and verified
        $existingDevice = Device::where('user_id', $user->id)
            ->where('ip_address', $ipAddress)
            ->where('device_name', $deviceName)
            ->where('device_type', $deviceType)
            ->first();

        if ($existingDevice && $existingDevice->is_verified) {
            // Device is verified - grant full access
            $existingDevice->update(['last_login' => now()]);
            
            $token = $user->createToken('auth_token', ['*'])->plainTextToken;
            
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
                'device_status' => 'verified',
                'message' => 'Login successful. Full access granted.'
            ]);
        } 
        elseif ($existingDevice && !$existingDevice->is_verified) {
            // Device exists but isn't verified
            $token = $user->createToken('auth_token', [])->plainTextToken;
            
            // Generate a new verification URL and resend the email
            $verificationUrl = $this->generateVerificationUrl($user, $existingDevice, $deviceName, $deviceType, $ipAddress);
            $this->sendVerificationEmail($user, $verificationUrl, $deviceName);
            
            return response()->json([
                'token' => $token,
                'device_status' => 'pending_verification',
                'message' => 'Device pending verification. Check your email.'
            ], 403);
        } 
        else {
            // New device - create record and send verification email
            $newDevice = Device::create([
                'user_id' => $user->id,
                'device_name' => $deviceName,
                'device_type' => $deviceType,
                'ip_address' => $ipAddress,
                'user_agent_data' => $userAgentData,
                'last_login' => now(),
                'is_verified' => false,
            ]);

            // Token with no abilities
            $token = $user->createToken('auth_token', [])->plainTextToken;

            // Generate verification URL and send email
            $verificationUrl = $this->generateVerificationUrl($user, $newDevice, $deviceName, $deviceType, $ipAddress);
            $emailSent = $this->sendVerificationEmail($user, $verificationUrl, $deviceName);
            
            $status = $emailSent ? 'verification_sent' : 'verification_failed';
            $message = $emailSent 
                ? 'New device detected. Verification email sent. Limited access until verified.' 
                : 'Failed to send verification email. Please try again later.';
            
            Log::info("New device login: $deviceName, Status: $status");
            
            return response()->json([
                'token' => $token,
                'device_status' => $status,
                'message' => $message
            ], $emailSent ? 403 : 500);
        }
    }
    
    /**
     * Generate a secure verification URL for device verification
     */
    private function generateVerificationUrl($user, $device, $deviceName, $deviceType, $ipAddress)
    {
        return URL::temporarySignedRoute(
            'device.verify',
            now()->addHours(24),
            [
                'device_id' => $device->device_id,
                'user_id' => $user->id,
                'device_name' => $deviceName,
                'device_type' => $deviceType,
                'ip_address' => $ipAddress,
            ]
        );
    }
    
    /**
     * Send the verification email
     */
    private function sendVerificationEmail($user, $verificationUrl, $deviceName)
    {
        try {
            Mail::to($user->email)->send(new NewDeviceNotification($user, $verificationUrl));
            Log::info("Verification email sent to {$user->email} for device: {$deviceName}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send verification email: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Verify a device from an email link
     */
    public function verifyDevice(Request $request)
    {
        // Check if URL signature is valid
        if (!$request->hasValidSignature()) {
            return response()->json([
                'message' => 'Invalid or expired verification link.',
                'error' => 'signature_invalid'
            ], 400);
        }
        
        try {
            // First try to find device by ID if provided
            $device = null;
            $user = User::findOrFail($request->user_id);
            
            if ($request->has('device_id')) {
                $device = Device::where('device_id', $request->device_id)->first();
            }
            
            // If not found by ID, find by other parameters
            if (!$device) {
                $device = Device::where('user_id', $user->id)
                    ->where('ip_address', $request->ip_address)
                    ->where('device_name', $request->device_name)
                    ->where('device_type', $request->device_type)
                    ->first();
            }
            
            // If still not found, create a new device
            if (!$device) {
                $device = Device::create([
                    'user_id' => $user->id,
                    'device_name' => $request->device_name,
                    'device_type' => $request->device_type,
                    'ip_address' => $request->ip_address,
                    'last_login' => now(),
                    'is_verified' => true,
                ]);
                
                Log::info("Created and verified new device for user ID: {$user->id}");
            } else {
                // Update existing device to verified
                $device->is_verified = true;
                $device->save();
                
                Log::info("Verified existing device ID: {$device->device_id} for user ID: {$user->id}");
            }
            
            // Create a token with full access
            $token = $user->createToken('verified_device_token', ['*'])->plainTextToken;
            
            // Return HTML response for browser or JSON for API
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Device verified successfully. You now have full access.',
                    'token' => $token
                ]);
            } else {
                return response()->view('device-verified', [
                    'message' => 'Your device has been successfully verified.',
                    'token' => $token
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Device verification failed: " . $e->getMessage());
            
            return response()->json([
                'message' => 'Device verification failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Logout user and revoke token
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}

