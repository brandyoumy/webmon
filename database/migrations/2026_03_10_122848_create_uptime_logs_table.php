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
        Schema::create('uptime_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('websites_id')->constrained('websites')->cascadeOnDelete();
            $table->integer('status_code')->nullable();
            $table->float('response_time')->nullable();
            $table->boolean('is_up')->default(false);
            $table->boolean('ssl_valid')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps(); 
            
            $table->index(['websites_id', 'checked_at']);
            $table->index('checked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uptime_logs');
    }
};
