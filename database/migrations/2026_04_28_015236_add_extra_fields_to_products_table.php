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
        Schema::table('products', function (Blueprint $table) {
            // Kolom Dasar POS Pro
            if (!Schema::hasColumn('products', 'cost_price')) {
                $table->decimal('cost_price', 15, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('products', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('products', 'condition')) {
                $table->string('condition', 20)->default('new')->after('description');
            }

            // Kolom Spesifik Apotik / Jasa / ATK
            if (!Schema::hasColumn('products', 'rack_number')) {
                $table->string('rack_number', 50)->nullable()->after('stock');
            }
            if (!Schema::hasColumn('products', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('rack_number');
            }
            if (!Schema::hasColumn('products', 'wholesale_price')) {
                $table->decimal('wholesale_price', 15, 2)->nullable()->after('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'cost_price', 
                'description', 
                'condition', 
                'rack_number', 
                'expiry_date', 
                'wholesale_price'
            ]);
        });
    }
};
