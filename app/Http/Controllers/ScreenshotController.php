<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class ScreenshotController extends Controller
{
    public function show($testId, $candidateId, $filename)
    {
        $path = "private/screenshots/test{$testId}/candidate{$candidateId}/{$filename}";

        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        $file = Storage::disk('local')->get($path);
        $mime = Storage::disk('local')->mimeType($path);

        return Response::make($file, 200)->header("Content-Type", $mime);
    }
}
