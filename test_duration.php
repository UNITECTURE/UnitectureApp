<?php

function parseDuration($durationStr)
{
    if (empty($durationStr)) return 0;
    
    $durationStr = strtolower($durationStr);
    $totalMinutes = 0;

    // Try "X Hrs Y Mins" or "X Hours Y Minutes"
    if (str_contains($durationStr, 'hrs') || str_contains($durationStr, 'mins') || str_contains($durationStr, 'hour')) {
            preg_match('/(\d+)\s*(?:hr|hour)/i', $durationStr, $hMatch);
            preg_match('/(\d+)\s*(?:min)/i', $durationStr, $mMatch);
            
            $h = isset($hMatch[1]) ? (int)$hMatch[1] : 0;
            $m = isset($mMatch[1]) ? (int)$mMatch[1] : 0;
            $totalMinutes = ($h * 60) + $m;
    } 
    // Try "2h 15m" or "2h" or "15m"
    else {
            preg_match('/(\d+)\s*h/i', $durationStr, $hMatch);
            // This is the tricky part I changed:
            preg_match('/(\d+)\s*m(?![a-z])/i', $durationStr, $mMatch); // Negative lookahead
            
            // Backup check
            if (empty($mMatch)) {
                 preg_match('/(\d+)\s*m/i', $durationStr, $mMatch);
            }

            $h = isset($hMatch[1]) ? (int)$hMatch[1] : 0;
            $m = isset($mMatch[1]) ? (int)$mMatch[1] : 0;

            $totalMinutes = ($h * 60) + $m;
    }

    return $totalMinutes;
}

echo "3h 45m: " . parseDuration("3h 45m") . "\n";
echo "2h 15m: " . parseDuration("2h 15m") . "\n";
echo "3 Hrs 45 Mins: " . parseDuration("3 Hrs 45 Mins") . "\n";
