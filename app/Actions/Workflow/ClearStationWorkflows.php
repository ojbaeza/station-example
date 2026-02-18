<?php

declare(strict_types=1);

namespace App\Actions\Workflow;

use Illuminate\Support\Facades\DB;

class ClearStationWorkflows
{
    public function handle(): void
    {
        DB::table('station_workflows')->delete();
    }
}
