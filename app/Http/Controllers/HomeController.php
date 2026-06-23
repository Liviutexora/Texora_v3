<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function forBusinesses()
    {
        return view('pages.for-businesses');
    }

    /**
     * View/download uploaded file
     *
     * @param Request $request
     * @param string $path (base64 encoded)
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function viewFile(Request $request, $path)
    {
        try {
            // Decode the path
            $filePath = base64_decode($path);
            
            if (!$filePath) {
                abort(404, 'File not found');
            }

            // Get the configured filesystem disk
            $disk = Setting::get('filesystem_disk', env('FILESYSTEM_DISK', 'public'));

            /** @var FilesystemAdapter $storageDisk */
            $storageDisk = Storage::disk($disk);

            // Check if file exists
            if (!$storageDisk->exists($filePath)) {
                abort(404, 'File not found');
            }

            // For S3, redirect to the public URL if available (more efficient)
            if ($disk === 's3') {
                try {
                    $s3Url = $storageDisk->url($filePath);
                    
                    // If URL is valid and not localhost, redirect to S3
                    if (!empty($s3Url) && !str_contains($s3Url, 'localhost')) {
                        return redirect($s3Url);
                    }
                } catch (\Exception $e) {
                    Log::warning('S3 redirect failed, serving file directly: ' . $e->getMessage());
                    // Continue to serve file directly if redirect fails
                }
            }

            // Get file contents
            $fileContents = $storageDisk->get($filePath);
            
            // Get MIME type with fallback
            $mimeType = 'application/octet-stream';
            try {
                if (method_exists($storageDisk, 'mimeType')) {
                    $mimeType = $storageDisk->mimeType($filePath);
                }
            } catch (\Exception $e) {
                // Fallback: determine from extension
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                $mimeTypes = [
                    'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif',
                    'pdf' => 'application/pdf', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'xls' => 'application/vnd.ms-excel', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'txt' => 'text/plain', 'csv' => 'text/csv', 'zip' => 'application/zip', 'rar' => 'application/x-rar-compressed',
                ];
                $mimeType = $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
            }
            
            $fileName = basename($filePath);

            // Return file response
            return response($fileContents, 200)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'inline; filename="' . $fileName . '"')
                ->header('Cache-Control', 'public, max-age=3600');
                
        } catch (\Exception $e) {
            Log::error('File view error: ' . $e->getMessage() . ' | Path: ' . ($path ?? 'N/A'));
            abort(404, 'File not found: ' . $e->getMessage());
        }
    }

    /**
     * Global file upload function - works with S3, public, and local disks
     * 
     * @param \Illuminate\Http\UploadedFile $file The uploaded file
     * @param string|null $disk Optional disk name (s3, public, local). If null, uses configured disk from settings
     * @param string|null $directory Optional directory path. If null, auto-determines based on file type
     * @param string|null $fileName Optional custom filename. If null, generates unique filename
     * @return array Returns array with 'success', 'path', 'url', 'message', and file metadata
     */
    public static function uploadFileGlobal($file, $disk = null, $directory = null, $fileName = null)
    {
        try {
            if (!$file || !$file->isValid()) {
                return [
                    'success' => false,
                    'message' => 'Invalid file uploaded.'
                ];
            }

            // Get disk from parameter or settings
            if (!$disk) {
                $disk = Setting::get('filesystem_disk', env('FILESYSTEM_DISK', 'public'));
            }

            // Update S3 config if using S3
            if ($disk === 's3') {
                config([
                    'filesystems.disks.s3.key' => Setting::get('s3_access_key_id', env('S3_ACCESS_KEY_ID')),
                    'filesystems.disks.s3.secret' => Setting::get('s3_secret_access_key', env('S3_SECRET_ACCESS_KEY')),
                    'filesystems.disks.s3.region' => Setting::get('s3_default_region', env('S3_DEFAULT_REGION')),
                    'filesystems.disks.s3.bucket' => Setting::get('s3_bucket', env('S3_BUCKET')),
                    'filesystems.disks.s3.url' => Setting::get('s3_url', env('S3_URL')),
                    'filesystems.disks.s3.endpoint' => Setting::get('s3_endpoint', env('S3_ENDPOINT')),
                ]);
            }

            /** @var FilesystemAdapter $storageDisk */
            $storageDisk = Storage::disk($disk);

            // Server-side MIME validation — reads actual file bytes, ignores client-supplied headers
            $allowedMimeTypes = [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/avif',
                'image/heic', 'image/svg+xml',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/plain', 'text/csv',
                'application/zip', 'application/x-rar-compressed', 'application/x-zip-compressed',
            ];

            $detectedMime = $file->getMimeType(); // uses finfo — reads file bytes
            if (!in_array($detectedMime, $allowedMimeTypes)) {
                return [
                    'success' => false,
                    'message' => "File type '{$detectedMime}' is not allowed.",
                ];
            }

            // Map detected MIME to a safe extension — never trust client extension
            $mimeToExt = [
                'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif',
                'image/webp' => 'webp', 'image/bmp' => 'bmp', 'image/avif' => 'avif',
                'image/heic' => 'heic', 'image/svg+xml' => 'svg',
                'application/pdf' => 'pdf',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                'application/vnd.ms-excel' => 'xls',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
                'text/plain' => 'txt', 'text/csv' => 'csv',
                'application/zip' => 'zip', 'application/x-zip-compressed' => 'zip',
                'application/x-rar-compressed' => 'rar',
            ];
            $safeExtension = $mimeToExt[$detectedMime] ?? 'bin';

            // Generate UUID-based filename — unpredictable, no overwrites
            if (!$fileName) {
                $fileName = Str::uuid() . '.' . $safeExtension;
            }

            // Determine directory based on detected MIME, not client-supplied extension
            if (!$directory) {
                if (str_starts_with($detectedMime, 'image/')) {
                    $directory = 'uploads/images';
                } elseif (in_array($detectedMime, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/plain', 'text/csv'])) {
                    $directory = 'uploads/documents';
                } elseif (in_array($detectedMime, ['application/zip', 'application/x-zip-compressed', 'application/x-rar-compressed'])) {
                    $directory = 'uploads/archives';
                } else {
                    $directory = 'uploads';
                }
            }

            // Upload file based on disk type
            $path = null;
            if ($disk === 's3') {
                $path = $storageDisk->putFileAs($directory, $file, $fileName);
                if (empty($path)) {
                    $fullPath = trim($directory . '/' . $fileName, '/');
                    $path = $storageDisk->put($fullPath, file_get_contents($file->getRealPath()), 'public');
                }
                // Set visibility for S3
                try {
                    $storageDisk->setVisibility($path, 'public');
                } catch (\Exception $e) {
                    Log::warning('Could not set S3 file visibility: ' . $e->getMessage());
                }
            } else {
                $path = $file->storeAs($directory, $fileName, $disk);
                // Set visibility for local/public
                try {
                    $storageDisk->setVisibility($path, 'public');
                } catch (\Exception $e) {
                    // Ignore visibility errors for local disks
                }
            }

            if (!$path) {
                return [
                    'success' => false,
                    'message' => 'Failed to upload file. Please check your storage configuration.'
                ];
            }

            // Generate URL
            $url = null;
            if ($disk === 's3') {
                $url = $storageDisk->url($path);
                if (empty($url) || str_contains($url, 'localhost')) {
                    $bucket = config('filesystems.disks.s3.bucket');
                    $region = config('filesystems.disks.s3.region');
                    $awsUrl = config('filesystems.disks.s3.url');
                    if ($awsUrl) {
                        $url = rtrim($awsUrl, '/') . '/' . $path;
                    } elseif ($bucket && $region) {
                        $url = "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
                    }
                }
            } else {
                if ($disk === 'public') {
                    // Keep public-file URLs host-agnostic so content can move between
                    // environments (dev/stage/prod) without hardcoded domains.
                    $url = $storageDisk->url($path);
                } else {
                    // Use signed URL for secure file access (expires in 24 hours)
                    $url = \Illuminate\Support\Facades\URL::signedRoute('file.view', ['path' => base64_encode($path)], now()->addHours(24));
                }
            }

            return [
                'success' => true,
                'message' => 'File uploaded successfully.',
                'path' => $path,
                'url' => $url,
                'disk' => $disk,
                'directory' => $directory,
                'file_name' => $fileName,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ];

        } catch (\Exception $e) {
            Log::error('Global upload error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ];
        }
    }

}