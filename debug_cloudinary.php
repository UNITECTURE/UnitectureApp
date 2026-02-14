<?php

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Cloudinary Configuration...\n\n";

$envUrl = env('CLOUDINARY_URL');
echo "env('CLOUDINARY_URL'): " . ($envUrl ? $envUrl : "NOT SET (or empty)") . "\n";

$configCloudUrl = config('cloudinary.cloud_url');
echo "config('cloudinary.cloud_url'): " . ($configCloudUrl ? $configCloudUrl : "NOT SET") . "\n";

$filesystemUrl = config('filesystems.disks.cloudinary.url');
echo "config('filesystems.disks.cloudinary.url'): " . ($filesystemUrl ? $filesystemUrl : "NOT SET") . "\n";

echo "\nAttempting to access cloudinary disk...\n";

try {
    $disk = Storage::disk('cloudinary');
    echo "Disk instance created successfully.\n";

    // Create a local temporary file
    $tempFile = tempnam(sys_get_temp_dir(), 'debug_cloudinary');
    file_put_contents($tempFile, 'This is a test file content for Cloudinary debug.');

    echo "Created temp file at: $tempFile\n";

    // Use putFile which mimics the controller
    echo "Attempting to upload temp file using putFile...\n";

    // mimic: $path = Storage::disk('cloudinary')->putFile('unitecture_users', $uploadedFile);
    // putFile expects a File/UploadedFile or string path? 
    // Laravel doc: putFile(path, file, options) where file is File|UploadedFile|string
    $path = $disk->putFile('unitecture_users', new \Illuminate\Http\File($tempFile));

    if ($path) {
        echo "Upload SUCCESS! Path: $path\n";
        $url = $disk->url($path);
        echo "URL: $url\n";

        // Clean up cloud file
        // echo "Deleting cloud file...\n";
        // $disk->delete($path);
        // echo "Cleanup SUCCESS.\n";
    } else {
        echo "Upload FAILED (returned false).\n";
    }

    // Clean up local file
    unlink($tempFile);

} catch (\Exception $e) {
    echo "ERROR: Cloudinary operation failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nDone.\n";
