<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidJobClass implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!class_exists($value)) {
            $fail('The :attribute must be a valid, existing class.');

            return;
        }

        if (!is_a($value, ShouldQueue::class, true)) {
            $fail('The :attribute must implement ShouldQueue.');
        }
    }
}
