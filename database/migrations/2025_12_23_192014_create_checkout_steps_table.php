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
        Schema::create('checkout_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('audit_id')->constrained('audits')->onDelete('cascade');
            $table->integer('step_number');
            $table->string('step_name');
            $table->text('url');
            $table->string('screenshot_path')->nullable();
            $table->integer('form_fields_count')->nullable();
            $table->json('errors_found')->nullable();
            $table->integer('load_time')->nullable();
            $table->boolean('successful')->default(true);
            $table->timestamps();

            $table->index(['audit_id', 'step_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkout_steps');
    }
};
