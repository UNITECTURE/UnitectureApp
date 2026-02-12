<?php
/**
 * Clear Cache Script for GoDaddy Production
 * 
 * Upload this file to your GoDaddy root directory and access it via browser:
 * https://hrms.unitecture.co/clear_cache.php
 * 
 * After running, DELETE this file for security.
 */

// Require Composer autoload
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__.'/bootstrap/app.php';

// Start output
echo "<!DOCTYPE html><html><head><title>Cache Clearer</title></head><body>";
echo "<h1>Laravel Cache Clearer</h1>";
echo "<pre>";

try {
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    
    echo "Clearing configuration cache...\n";
    $kernel->call('config:clear');
    echo "✓ Configuration cache cleared\n\n";
    
    echo "Clearing application cache...\n";
    $kernel->call('cache:clear');
    echo "✓ Application cache cleared\n\n";
    
    echo "Clearing route cache...\n";
    $kernel->call('route:clear');
    echo "✓ Route cache cleared\n\n";
    
    echo "Clearing view cache...\n";
    $kernel->call('view:clear');
    echo "✓ View cache cleared\n\n";
    
    echo "Clearing compiled classes...\n";
    $kernel->call('clear-compiled');
    echo "✓ Compiled classes cleared\n\n";
    
    echo "<strong style='color: green;'>ALL CACHES CLEARED SUCCESSFULLY!</strong>\n\n";
    echo "<strong style='color: red;'>IMPORTANT: Delete this file (clear_cache.php) now for security!</strong>\n";
    
} catch (Exception $e) {
    echo "<strong style='color: red;'>ERROR: " . $e->getMessage() . "</strong>\n";
}

echo "</pre></body></html>";
