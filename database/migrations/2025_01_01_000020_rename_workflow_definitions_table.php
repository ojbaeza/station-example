<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::rename('workflow_definitions', 'station_workflow_definitions');

        Schema::table('station_workflow_definitions', static function (Blueprint $table): void {
            $table->string('connection', 100)->nullable()->after('timeout');
        });
    }

    public function down(): void
    {
        Schema::table('station_workflow_definitions', static function (Blueprint $table): void {
            $table->dropColumn('connection');
        });

        Schema::rename('station_workflow_definitions', 'workflow_definitions');
    }
};
