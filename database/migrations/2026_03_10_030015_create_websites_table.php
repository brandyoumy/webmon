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
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('company_name')->nullable();
            $table->string('pic_email')->nullable();
            $table->string('pic_phone')->nullable();
            $table->boolean('check_ssl')->default(true);
            $table->integer('check_interval')->default(5);
            $table->date('domain_expires_at')->nullable();
            $table->boolean('is_up')->default(true);
            $table->boolean('ssl_valid')->default(true);
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};
