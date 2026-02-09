<?php

require 'vendor/autoload.php';

use Carbon\Carbon;

// Mock Logs
$scenarios = [
    'Standard' => [
        'first' => '2023-10-02 09:00:00',
        'last' => '2023-10-02 18:00:00',
    ],
    'Next Day Shift' => [
        'first' => '2023-10-02 20:00:00',
        'last' => '2023-10-03 04:00:00',
    ],
    'Inverted (If Sort Fail)' => [
        'first' => '2023-10-02 18:00:00', // Treated as first in list
        'last' => '2023-10-02 09:00:00',  // Treated as last in list
    ],
    'Same Time' => [
        'first' => '2023-10-02 09:00:00',
        'last' => '2023-10-02 09:00:00',
    ]
];

echo "--- Biometric Calculation Tests ---\n";

foreach ($scenarios as $name => $times) {
    try {
        $first = Carbon::parse($times['first']);
        $last = Carbon::parse($times['last']);

        // Test default diffInMinutes (abs = true)
        $diffDefault = $first->diffInMinutes($last);

        // Test explicit false (if code was changed or behaving odd)
        $diffRaw = $first->diffInMinutes($last, false);

        echo "Scenario: $name\n";
        echo "  First: " . $first->toDateTimeString() . "\n";
        echo "  Last:  " . $last->toDateTimeString() . "\n";
        echo "  Diff (Default): $diffDefault\n";
        echo "  Diff (Raw/False): $diffRaw\n";
        echo "\n";
    } catch (\Exception $e) {
        echo "Error in $name: " . $e->getMessage() . "\n";
    }
}

echo "--- Manual Check Tests ---\n";

function parseDuration($durationStr)
{
    if (empty($durationStr))
        return 0;

    $durationStr = strtolower($durationStr);
    $totalMinutes = 0;

    if (str_contains($durationStr, 'hrs') || str_contains($durationStr, 'mins') || str_contains($durationStr, 'hour')) {
        preg_match('/(\d+)\s*(?:hr|hour)/i', $durationStr, $hMatch);
        preg_match('/(\d+)\s*(?:min)/i', $durationStr, $mMatch);

        $h = isset($hMatch[1]) ? (int) $hMatch[1] : 0;
        $m = isset($mMatch[1]) ? (int) $mMatch[1] : 0;
        $totalMinutes = ($h * 60) + $m;
    } else {
        preg_match('/(\d+)\s*h/i', $durationStr, $hMatch);
        preg_match('/(\d+)\s*m/i', $durationStr, $mMatch);

        $h = isset($hMatch[1]) ? (int) $hMatch[1] : 0;
        preg_match('/(\d+)\s*m(?![a-z])/i', $durationStr, $m2Match);

        $m = isset($mMatch[1]) ? (int) $mMatch[1] : 0;
        if ($m == 0 && isset($m2Match[1]))
            $m = (int) $m2Match[1];

        $totalMinutes = ($h * 60) + $m;
    }

    return $totalMinutes;
}

$manuals = [
    "2 Hrs 30 Mins",
    "9 Hours",
    "-2 Hrs", // Malformed input check
    "Text Only",
    "50m",
    "2h 15m"
];

foreach ($manuals as $m) {
    echo "Input: '$m' -> Minutes: " . parseDuration($m) . "\n";
}
