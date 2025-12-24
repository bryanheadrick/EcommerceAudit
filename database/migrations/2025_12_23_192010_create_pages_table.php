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
        Schema::create('pages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('audit_id')->constrained('audits')->onDelete('cascade');
            $table->text('url');
            $table->integer('status_code')->nullable();
            $table->string('title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('h1')->nullable();
            $table->integer('load_time')->nullable();
            $table->string('screenshot_path')->nullable();
            $table->text('html_excerpt')->nullable();
            $table->timestamp('crawled_at')->nullable();
            $table->timestamps();

            $table->index('audit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
