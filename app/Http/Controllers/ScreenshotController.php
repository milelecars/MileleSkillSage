<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;

class ScreenshotController extends Controller
{
    public function show($testId, $candidateId, $filename)
    {
        $path = storage_path("app/private/screenshots/test{$testId}/candidate{$candidateId}/{$filename}");

        if (!File::exists($path)) {
            abort(404, 'Screenshot not found.');
        }

        $mimeType = File::mimeType($path);

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=604800',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }
}
