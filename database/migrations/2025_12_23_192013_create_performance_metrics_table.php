<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('performance_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('page_id')->constrained('pages')->onDelete('cascade');
            $table->enum('device_type', ['mobile', 'desktop']);
            $table->float('lcp')->nullable();
            $table->float('fid')->nullable();
            $table->float('cls')->nullable();
            $table->float('fcp')->nullable();
            $table->integer('ttfb')->nullable();
            $table->float('speed_index')->nullable();
            $table->integer('total_blocking_time')->nullable();
            $table->integer('lighthouse_performance_score')->nullable();
            $table->integer('lighthouse_accessibility_score')->nullable();
            $table->integer('lighthouse_seo_score')->nullable();
            $table->integer('lighthouse_best_practices_score')->nullable();
            $table->json('lighthouse_json')->nullable();
            $table->timestamps();

            $table->index(['page_id', 'device_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_metrics');
    }
};
