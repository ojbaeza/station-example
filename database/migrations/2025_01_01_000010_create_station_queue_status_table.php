<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('station_queue_status', static function (Blueprint $table): void {
            $table->string('queue', 100)->primary();
            $table->boolean('paused')->default(false);
            $table->string('paused_by')->nullable(); // user ID or 'system'
            $table->text('pause_reason')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('resume_at')->nullable(); // For scheduled resume
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('station_queue_status');
    }
};
