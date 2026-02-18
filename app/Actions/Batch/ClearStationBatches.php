<?php

declare(strict_types=1);

namespace App\Actions\Batch;

use Illuminate\Support\Facades\DB;

class ClearStationBatches
{
    public function handle(): void
    {
        DB::table('station_batches')->delete();
    }
}
