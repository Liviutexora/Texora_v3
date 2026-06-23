<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Storage;

Route::prefix('v1')->group(function () {

    // Public routes with rate limiting
    Route::middleware(['throttle:60,1'])->group(function () {
        Route::post('/auth/login', [AuthController::class, 'login']);
        Route::post('/auth/register', [AuthController::class, 'register']);
    });

    // Protected routes with rate limiting
    Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });

    // File upload route with rate limiting and authentication
    Route::middleware(['auth:sanctum', 'throttle:10,1'])->post('/upload', function (Request $request) {
        $request->validate([
            'file' => 'required|file|max:2048',
        ]);

        $file = $request->file('file');

        // Validate MIME by reading actual file bytes — not client headers
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        $detectedMime = $file->getMimeType();
        if (!in_array($detectedMime, $allowedMimes)) {
            return response()->json(['success' => false, 'message' => 'File type not allowed'], 422);
        }

        $mimeToExt = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/pdf' => 'pdf'];
        $filename  = \Illuminate\Support\Str::uuid() . '.' . ($mimeToExt[$detectedMime] ?? 'bin');

        $path = Storage::putFileAs('uploads', $file, $filename);
        if (!$path) {
            return response()->json(['success' => false, 'message' => 'Upload failed'], 500);
        }
        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data'    => ['path' => $path],
        ]);
    });

}); // end v1

