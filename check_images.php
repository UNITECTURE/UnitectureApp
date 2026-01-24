<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = \App\Models\User::whereNotNull('profile_image')->get();

echo "Users with profile images:\n";
echo "============================\n\n";

if ($users->isEmpty()) {
    echo "No users with profile images found.\n";
} else {
    foreach ($users as $user) {
        echo "Name: " . $user->full_name . "\n";
        echo "Image URL: " . $user->profile_image . "\n";
        echo "---\n";
    }
}

echo "\n\nChecking Cloudinary folder 'unitecture_users':\n";
echo "Visit: https://console.cloudinary.com/pm/ddvkwscrr/media_library/folders/home\n";
echo "to see uploaded images in the Media Library\n";
?>
