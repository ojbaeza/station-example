<?php

declare(strict_types=1);

use DG\BypassFinals;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

// Create the testing SQLite database and run migrations once before the suite.

require __DIR__ . '/../vendor/autoload.php';

BypassFinals::enable();

$dbPath = __DIR__ . '/../database/testing.sqlite';

if (!file_exists($dbPath)) {
    touch($dbPath);
}

// Boot the app and migrate
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

Artisan::call('migrate:fresh', ['--force' => true]);
