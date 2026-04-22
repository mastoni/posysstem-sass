<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Tenant
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete(); // Branch
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            
            $table->integer('quantity_initial');
            $table->integer('quantity_remaining');
            $table->decimal('cost_price', 15, 2);
            
            $table->string('batch_number')->nullable();
            $table->date('expired_at')->nullable();
            $table->timestamps();
            
            $table->index(['product_id', 'branch_id', 'created_at']);
        });

        // Add batch_id to order_items to track which batch was used for COGS
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('stock_batch_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('cost_price', 15, 2)->nullable(); // Captured COGS at time of sale
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['stock_batch_id']);
            $table->dropColumn(['stock_batch_id', 'cost_price']);
        });

        Schema::dropIfExists('stock_batches');
    }
};
