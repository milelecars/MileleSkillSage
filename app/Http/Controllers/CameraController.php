<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CameraController extends Controller
{
    public function updatePermission(Request $request)
    {
        Log::info('Update Permission Request:', [
            'input' => $request->all(),
            'session_before' => $request->session()->all()
        ]);

        $request->session()->put('camera_permission', [
            'granted' => $request->input('granted', false),
            'deviceId' => $request->input('deviceId'),
            'streamActive' => $request->input('streamActive', false)
        ]);

        Log::info('Session After Update:', [
            'session' => $request->session()->all(),
            'camera_permission' => $request->session()->get('camera_permission')
        ]);

        return response()->json([
            'success' => true,
            'permission' => $request->session()->get('camera_permission')
        ]);
    }

    public function checkPermission(Request $request)
    {
        Log::info('Check Permission Request:', [
            'session' => $request->session()->all(),
            'camera_permission' => $request->session()->get('camera_permission')
        ]);

        return response()->json([
            'permission' => $request->session()->get('camera_permission', [
                'granted' => false,
                'deviceId' => null,
                'streamActive' => false
            ])
        ]);
    }
}