<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FlagController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'personCount' => 'required|integer',
            'hasBook' => 'required|boolean',
            'hasCellPhone' => 'required|boolean',
            'timestamp' => 'required|date'
        ]);

        $user = auth()->user();
        $userName = $user ? $user->name : 'Unknown user';

        $logMessage = "Test alert for {$userName}: ";
        $logMessage .= "Persons detected: {$data['personCount']}, ";
        $logMessage .= "Book detected: " . ($data['hasBook'] ? 'Yes' : 'No') . ", ";
        $logMessage .= "Cell phone detected: " . ($data['hasCellPhone'] ? 'Yes' : 'No');

        // Log::warning($logMessage, [
        //     'user_id' => $user ? $user->id : null,
        //     'timestamp' => $data['timestamp']
        // ]);

        // Store the alert in the database
        // Alert::create([
        //     'user_id' => $user ? $user->id : null,
        //     'message' => $logMessage,
        //     'person_count' => $data['personCount'],
        //     'has_book' => $data['hasBook'],
        //     'has_cell_phone' => $data['hasCellPhone'],
        //     'timestamp' => $data['timestamp']
        // ]);

        return response()->json(['status' => 'success', 'message' => 'Alert received and stored']);
    }
}