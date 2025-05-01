<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CameraController extends Controller
{
    public function updatePermission(Request $request)
    {
        $testId = $request->input('testId');
        $candidateId = $request->input('candidateId');

        $sessionKey = "camera_permission_{$candidateId}_{$testId}";

        Log::info('Update Permission Request:', [
            'input' => $request->all(),
            'session_before' => $request->session()->all()
        ]);

        $request->session()->put($sessionKey, [
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
            'permission' => $request->session()->get($sessionKey)
        ]);        
    }

    public function checkPermission(Request $request)
    {
        Log::info('Check Permission Request:', [
            'session' => $request->session()->all(),
            'camera_permission' => $request->session()->get('camera_permission')
        ]);

        $testId = $request->query('testId');
        $candidateId = $request->query('candidateId');
    
        if (!$testId || !$candidateId) {
            return response()->json(['error' => 'Missing testId or candidateId'], 400);
        }
    
        $sessionKey = "camera_permission_{$candidateId}_{$testId}";
    
        return response()->json([
            'permission' => $request->session()->get($sessionKey, [
                'granted' => false,
                'deviceId' => null,
                'streamActive' => false
            ])
        ]);
    }
}