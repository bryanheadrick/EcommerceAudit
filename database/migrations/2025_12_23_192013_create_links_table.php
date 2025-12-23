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
        Schema::create('links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('audit_id')->constrained('audits')->onDelete('cascade');
            $table->foreignUuid('source_page_id')->constrained('pages')->onDelete('cascade');
            $table->text('destination_url');
            $table->string('link_text')->nullable();
            $table->enum('link_type', ['internal', 'external', 'asset']);
            $table->integer('status_code')->nullable();
            $table->boolean('is_broken')->default(false);
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->index(['audit_id', 'is_broken']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
