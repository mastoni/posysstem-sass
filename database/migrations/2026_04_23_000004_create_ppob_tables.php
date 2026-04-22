<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PPOB Products (Synced from provider like Digiflazz/IAK)
        Schema::create('ppob_products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // SKU from provider
            $table->string('name');
            $table->string('category'); // Pulsa, Data, PLN, etc.
            $table->string('brand'); // Telkomsel, XL, etc.
            $table->decimal('price_buy', 15, 2);
            $table->decimal('price_sell', 15, 2); // Markup price
            $table->string('provider'); // digiflazz, iak, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // PPOB Transactions
        Schema::create('ppob_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ppob_product_code');
            $table->string('customer_number'); // Phone or ID Pelanggan
            $table->string('ref_id')->unique(); // Internal ref
            $table->string('provider_ref_id')->nullable(); // Provider ref
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('pending'); // pending, success, failed
            $table->text('response_msg')->nullable();
            $table->timestamps();
        });
        
        // Add PPOB markup settings to User/Tenant
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('ppob_markup', 10, 2)->default(500.00); // Default profit per transaction
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ppob_markup');
        });
        Schema::dropIfExists('ppob_transactions');
        Schema::dropIfExists('ppob_products');
    }
};
