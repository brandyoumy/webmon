<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete()->after('company_name');
            $table->dropColumn('package');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->string('package')->nullable()->after('company_name');
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });
    }
};
