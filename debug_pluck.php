<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Pluck Return Type:\n";
// Create a fake range that covers the existing holiday Jan 26
$start = \Carbon\Carbon::parse('2026-01-01');
$end = \Carbon\Carbon::parse('2026-01-31');

$holidays = \App\Models\Holiday::whereBetween('date', [$start, $end])->pluck('date')->toArray();

echo "Count: " . count($holidays) . "\n";
if (count($holidays) > 0) {
    $first = $holidays[0];
    echo "First Item Type: " . gettype($first) . "\n";
    if (is_object($first)) {
        echo "Class: " . get_class($first) . "\n";
        echo "Value: " . $first . "\n";
    } else {
        echo "Value: " . $first . "\n";
    }

    echo "Checking in_array match:\n";
    $search = "2026-01-26";
    $match = in_array($search, $holidays);
    echo "in_array('$search', \$holidays) => " . ($match ? "TRUE" : "FALSE") . "\n";
} else {
    echo "No holidays found in Jan 2026 (unexpected given user input).\n";
}
