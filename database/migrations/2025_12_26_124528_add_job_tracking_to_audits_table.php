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
        Schema::table('audits', function (Blueprint $table) {
            $table->integer('jobs_total')->default(0);
            $table->integer('jobs_completed')->default(0);
            $table->integer('jobs_failed')->default(0);
            $table->text('current_step')->nullable();
            $table->text('error_message')->nullable();
        });
    }
};
