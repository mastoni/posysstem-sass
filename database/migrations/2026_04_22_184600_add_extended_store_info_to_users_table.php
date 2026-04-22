<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('store_type')->nullable();
            $table->text('store_address')->nullable();
            $table->string('store_phone')->nullable();
            $table->boolean('is_setup_completed')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['store_type', 'store_address', 'store_phone', 'is_setup_completed']);
        });
    }
};
