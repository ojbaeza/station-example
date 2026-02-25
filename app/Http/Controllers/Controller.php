<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Throwable;

abstract class Controller
{
    protected function safeErrorMessage(Throwable $e): string
    {
        return app()->environment('local', 'testing')
            ? $e->getMessage()
            : 'An internal error occurred.';
    }
}
