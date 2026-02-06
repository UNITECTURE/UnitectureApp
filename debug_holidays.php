<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "ALL Holidays in DB:\n";
$holidays = \App\Models\Holiday::all();
foreach ($holidays as $h) {
    echo "ID: {$h->id}, Name: {$h->name}, Date: " . ($h->date ? $h->date->format('Y-m-d') : 'NULL') . "\n";
}
