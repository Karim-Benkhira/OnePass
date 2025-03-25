<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\NewDeviceNotification;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class DeviceController extends Controller
{
    
    public function checkDevice(Request $request)
    {
        $user = Auth::user();

        // Get device details
        $agent = new Agent();
        $deviceName = $agent->device() ?: 'Unknown Device';
        $deviceType = $agent->isMobile() ? 'Mobile' : 'Desktop';
        $ipAddress = $request->ip();
        $userAgentData = $request->header('User-Agent');

        
        $existingDevice = Device::where('user_id', $user->id)
            ->where('ip_address', $ipAddress)
            ->where('device_name', $deviceName)
            ->where('device_type', $deviceType)
            ->first();

        if ($existingDevice) {
            if (!$existingDevice->is_verified) {
                return response()->json(['message' => 'Device pending verification. Check your email.'], 403);
            }
            $existingDevice->update(['last_login' => now()]);
            return response()->json(['message' => 'Login successful']);
        }

        $verificationUrl = URL::temporarySignedRoute(
            'devices.verify',
            now()->addMinutes(30), 
            [
                'user_id' => $user->id,
                'device_name' => $deviceName,
                'device_type' => $deviceType,
                'ip_address' => $ipAddress,
                'user_agent_data' => $userAgentData,
            ]
        );

        Mail::to($user->email)->send(new NewDeviceNotification($user, $verificationUrl));

        return response()->json(['message' => 'New device detected. Verification email sent.'], 403);
    }

    /**
     * Verify a new device and store it.
     */
    public function verifyDevice(Request $request)
    {
        if (!$request->hasValidSignature()) {
            return response()->json(['message' => 'Invalid or expired verification link.'], 400);
        }

        $user = Auth::user();
        $deviceData = $request->only(['device_name', 'device_type', 'ip_address', 'user_agent_data']);

        // Store the verified device
        Device::create([
            'user_id' => $user->id,
            'device_name' => $deviceData['device_name'],
            'device_type' => $deviceData['device_type'],
            'ip_address' => $deviceData['ip_address'],
            'user_agent_data' => $deviceData['user_agent_data'],
            'last_login' => now(),
            'is_verified' => true,
        ]);

        return response()->json(['message' => 'Device verified successfully. You can now log in.']);
    }

    /**
     * List all verified devices of the user.
     */
    public function listDevices()
    {
        $devices = Auth::user()->devices()->where('is_verified', true)->get();
        return response()->json($devices);
    }
}
