<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Station\Enums\Driver;

class DriverQueueRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'driver' => 'required|string|in:' . Driver::validationIn(),
            'queue' => 'nullable|string|max:255',
        ];
    }
}
