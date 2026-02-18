<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WorkflowDefinition extends Model
{
    use HasUuids;

    protected $table = 'station_workflow_definitions';

    protected $fillable = [
        'name',
        'description',
        'steps',
        'timeout',
        'connection',
    ];

    protected function casts(): array
    {
        return [
            'steps' => 'array',
            'timeout' => 'integer',
        ];
    }
}
