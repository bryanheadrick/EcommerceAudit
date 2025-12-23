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
        Schema::create('issues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('audit_id')->constrained('audits')->onDelete('cascade');
            $table->foreignUuid('page_id')->nullable()->constrained('pages')->onDelete('cascade');
            $table->enum('category', ['performance', 'mobile', 'seo', 'checkout', 'links', 'accessibility']);
            $table->enum('severity', ['critical', 'high', 'medium', 'low', 'info']);
            $table->string('title');
            $table->text('description');
            $table->text('recommendation');
            $table->string('affected_element')->nullable();
            $table->string('screenshot_path')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['audit_id', 'category', 'severity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
