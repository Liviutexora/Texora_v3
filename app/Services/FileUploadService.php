<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class FileUploadService
{
    /**
     * Handle file upload for Filament FileUpload component
     * Supports S3, public, and local disks
     * 
     * @param mixed $file The uploaded file (string path, TemporaryUploadedFile, or UploadedFile)
     * @param string $directory The directory to store the file (e.g., 'sliders', 'uploads/images')
     * @param string|null $disk Optional disk name. If null, uses configured disk from settings
     * @param string|null $customFilename Optional custom filename. If null, generates unique filename
     * @return string The stored file path
     * @throws \Exception
     */
    public static function handleFileUpload($file, string $directory, ?string $disk = null, ?string $customFilename = null): string
    {
        // Get disk from parameter or settings
        if (!$disk) {
            $disk = Setting::get('filesystem_disk', config('filesystems.default', 'public'));
        }

        // Update S3 config if using S3
        if ($disk === 's3') {
            self::updateS3Config();
        }

        // Generate filename if not provided
        if ($customFilename) {
            $newFilename = $customFilename;
            $extension = pathinfo($customFilename, PATHINFO_EXTENSION);
        } else {
            if (is_string($file)) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $originalName = pathinfo($file, PATHINFO_FILENAME);
            } elseif ($file instanceof TemporaryUploadedFile) {
                $extension = $file->getClientOriginalExtension();
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            } else {
                $extension = $file->getClientOriginalExtension();
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            }
            $newFilename = time() . '_' . uniqid() . '_' . str()->slug($originalName) . '.' . $extension;
        }

        $fullPath = trim($directory . '/' . $newFilename, '/');

        // Handle different file types
        if (is_string($file)) {
            return self::handleStringPath($file, $directory, $newFilename, $fullPath, $disk);
        } elseif ($file instanceof TemporaryUploadedFile) {
            return self::handleTemporaryFile($file, $directory, $newFilename, $fullPath, $disk);
        } else {
            return self::handleRegularFile($file, $directory, $newFilename, $fullPath, $disk);
        }
    }

    /**
     * Update S3 configuration from settings
     */
    protected static function updateS3Config(): void
    {
        config([
            'filesystems.disks.s3.key' => Setting::get('s3_access_key_id', config('filesystems.disks.s3.key')),
            'filesystems.disks.s3.secret' => Setting::get('s3_secret_access_key', config('filesystems.disks.s3.secret')),
            'filesystems.disks.s3.region' => Setting::get('s3_default_region', config('filesystems.disks.s3.region')),
            'filesystems.disks.s3.bucket' => Setting::get('s3_bucket', config('filesystems.disks.s3.bucket')),
            'filesystems.disks.s3.url' => Setting::get('s3_url', config('filesystems.disks.s3.url')),
            'filesystems.disks.s3.endpoint' => Setting::get('s3_endpoint', config('filesystems.disks.s3.endpoint')),
            'filesystems.disks.s3.use_path_style_endpoint' => (bool)Setting::get('s3_use_path_style_endpoint', config('filesystems.disks.s3.use_path_style_endpoint', false)),
        ]);
    }

    /**
     * Handle string path (file already on storage)
     */
    protected static function handleStringPath(string $file, string $directory, string $newFilename, string $fullPath, string $disk): string
    {
        if ($disk === 's3') {
            // Copy file from temporary S3 location to final location
            Storage::disk($disk)->copy($file, $fullPath);

            // Set public visibility
            try {
                Storage::disk($disk)->setVisibility($fullPath, 'public');
            } catch (\Exception $e) {
                // Ignore visibility errors if already set
            }

            // Delete temporary file
            try {
                Storage::disk($disk)->delete($file);
            } catch (\Exception $e) {
                // Ignore if deletion fails
            }

            return $fullPath;
        } else {
            // String path for local/public disk
            $possiblePaths = [
                storage_path('app/' . $file),
                storage_path('app/private/' . $file),
                storage_path('app/livewire-tmp/' . $file),
                storage_path('app/public/' . $file),
            ];

            $foundPath = null;
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    $foundPath = $path;
                    break;
                }
            }

            if ($foundPath) {
                $fileContent = file_get_contents($foundPath);
                $storedPath = Storage::disk($disk)->put($fullPath, $fileContent, 'public');

                // Ensure we return the path, not a boolean
                if (is_bool($storedPath) && $storedPath === true) {
                    $storedPath = $fullPath;
                }

                // Set visibility
                try {
                    Storage::disk($disk)->setVisibility($storedPath, 'public');
                } catch (\Exception $e) {
                    // Ignore visibility errors
                }

                return $storedPath;
            } else {
                throw new \Exception('File not found: ' . $file);
            }
        }
    }

    /**
     * Handle TemporaryUploadedFile
     */
    protected static function handleTemporaryFile(TemporaryUploadedFile $file, string $directory, string $newFilename, string $fullPath, string $disk): string
    {
        if ($disk === 's3') {
            // For S3, file is likely already on S3 (uploaded directly by Filament)
            $tempFilename = $file->getFilename();

            // Try multiple possible S3 paths
            $possibleS3Paths = [
                'livewire-tmp/' . $tempFilename,
                $tempFilename,
                'livewire-tmp/' . basename($tempFilename),
            ];

            // Also try to extract from getRealPath if it contains the path
            try {
                $realPath = $file->getRealPath();
                if ($realPath) {
                    $pathParts = explode('/', $realPath);
                    $filenameFromPath = end($pathParts);
                    if ($filenameFromPath) {
                        $possibleS3Paths[] = 'livewire-tmp/' . $filenameFromPath;
                    }

                    if (str_contains($realPath, 'livewire-tmp')) {
                        $tmpPart = substr($realPath, strpos($realPath, 'livewire-tmp'));
                        $possibleS3Paths[] = str_replace('\\', '/', $tmpPart);
                    }
                }
            } catch (\Exception $e) {
                // Ignore if getRealPath fails
            }

            // Check each possible path on S3
            $foundS3Path = null;
            foreach ($possibleS3Paths as $s3Path) {
                $s3Path = trim(str_replace('\\', '/', $s3Path), '/');
                if (!str_starts_with($s3Path, 'livewire-tmp/')) {
                    $s3Path = 'livewire-tmp/' . $s3Path;
                }

                if (Storage::disk($disk)->exists($s3Path)) {
                    $foundS3Path = $s3Path;
                    break;
                }
            }

            if ($foundS3Path) {
                // File exists on S3, copy it to final location
                Storage::disk($disk)->copy($foundS3Path, $fullPath);

                // Set public visibility
                try {
                    Storage::disk($disk)->setVisibility($fullPath, 'public');
                } catch (\Exception $e) {
                    // Ignore visibility errors if already set
                }

                // Delete temporary file
                try {
                    Storage::disk($disk)->delete($foundS3Path);
                } catch (\Exception $e) {
                    // Ignore if deletion fails
                }

                return $fullPath;
            } else {
                // File not found on S3, try local path as fallback
                try {
                    $tempPath = $file->getRealPath();
                    if ($tempPath && file_exists($tempPath)) {
                        $fileContent = file_get_contents($tempPath);
                        $storedPath = Storage::disk($disk)->put($fullPath, $fileContent, [
                            'visibility' => 'public',
                            'ACL' => 'public-read'
                        ]);

                        try {
                            Storage::disk($disk)->setVisibility($storedPath, 'public');
                        } catch (\Exception $e) {
                            // Ignore visibility errors if already set
                        }

                        return $storedPath;
                    }
                } catch (\Exception $e) {
                    // Ignore local file errors
                }

                throw new \Exception('File not found on S3. Temp filename: ' . $tempFilename . '. Checked paths: ' . implode(', ', $possibleS3Paths));
            }
        } else {
            // For public/local disks, use storeAs method
            $storedPath = $file->storeAs($directory, $newFilename, $disk);

            // Ensure we have a valid path string (not boolean or empty)
            if (empty($storedPath) || !is_string($storedPath) || $storedPath === true || $storedPath === 1) {
                $storedPath = $fullPath;
            }

            // Set visibility
            try {
                Storage::disk($disk)->setVisibility($storedPath, 'public');
            } catch (\Exception $e) {
                // Ignore visibility errors for local disks
            }

            return $storedPath;
        }
    }

    /**
     * Handle regular uploaded file
     */
    protected static function handleRegularFile($file, string $directory, string $newFilename, string $fullPath, string $disk): string
    {
        if ($disk === 's3') {
            // For S3, use putFileAs with ACL
            $storedPath = Storage::disk($disk)
                ->putFileAs($directory, $file, $newFilename, ['visibility' => 'public', 'ACL' => 'public-read']);

            // Ensure public visibility
            try {
                Storage::disk($disk)->setVisibility($storedPath, 'public');
            } catch (\Exception $e) {
                // Ignore visibility errors if already set
            }

            return $storedPath;
        } else {
            // For public/local disks, use storeAs method
            $storedPath = $file->storeAs($directory, $newFilename, $disk);

            // Ensure we have a valid path string (not boolean or empty)
            if (empty($storedPath) || !is_string($storedPath) || $storedPath === true || $storedPath === 1) {
                $storedPath = $fullPath;
            }

            // Set visibility
            try {
                Storage::disk($disk)->setVisibility($storedPath, 'public');
            } catch (\Exception $e) {
                // Ignore visibility errors for local disks
            }

            return $storedPath;
        }
    }
}

