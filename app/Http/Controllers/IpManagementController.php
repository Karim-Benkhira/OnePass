<?php

namespace App\Http\Controllers;

use App\Models\IpManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IpManagementController extends Controller
{
    public function index()
    {
        $ips = Auth::user()->ipManagements()->get();
        return response()->json($ips);
    }

    public function addToWhitelist(Request $request)
    {
        $validated = $request->validate([
            'ip_address' => 'required|ip',
            'description' => 'nullable|string|max:255'
        ]);

        $ip = Auth::user()->ipManagements()->updateOrCreate(
            ['ip_address' => $validated['ip_address']],
            [
                'status' => 'whitelist',
                'description' => $validated['description'] ?? null
            ]
        );

        return response()->json($ip);
    }

    public function addToBlacklist(Request $request)
    {
        $validated = $request->validate([
            'ip_address' => 'required|ip',
            'description' => 'nullable|string|max:255'
        ]);

        $ip = Auth::user()->ipManagements()->updateOrCreate(
            ['ip_address' => $validated['ip_address']],
            [
                'status' => 'blacklist',
                'description' => $validated['description'] ?? null
            ]
        );

        return response()->json($ip);
    }

    public function remove(Request $request)
    {
        $validated = $request->validate([
            'ip_address' => 'required|ip'
        ]);

        Auth::user()->ipManagements()
            ->where('ip_address', $validated['ip_address'])
            ->delete();

        return response()->json(['message' => 'IP removed successfully']);
    }

    public function checkIp(string $ip)
    {
        $ipStatus = Auth::user()->ipManagements()
            ->where('ip_address', $ip)
            ->first();

        if (!$ipStatus) {
            return response()->json(['status' => 'neutral']);
        }

        return response()->json([
            'status' => $ipStatus->status,
            'description' => $ipStatus->description
        ]);
    }
} 