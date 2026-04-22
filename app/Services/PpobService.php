<?php

namespace App\Services;

use App\Models\PpobProduct;
use App\Models\PpobTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PpobService
{
    /**
     * Fetch and sync products from provider (Mocked)
     */
    public function syncProducts()
    {
        // Example product list from provider
        $mockProducts = [
            ['code' => 'TSEL5', 'name' => 'Telkomsel 5rb', 'category' => 'Pulsa', 'brand' => 'Telkomsel', 'price' => 5300],
            ['code' => 'TSEL10', 'name' => 'Telkomsel 10rb', 'category' => 'Pulsa', 'brand' => 'Telkomsel', 'price' => 10300],
            ['code' => 'PLN20', 'name' => 'PLN Token 20rb', 'category' => 'PLN', 'brand' => 'PLN', 'price' => 20000],
        ];

        foreach ($mockProducts as $p) {
            PpobProduct::updateOrCreate(
                ['code' => $p['code']],
                [
                    'name' => $p['name'],
                    'category' => $p['category'],
                    'brand' => $p['brand'],
                    'price_buy' => $p['price'],
                    'price_sell' => $p['price'] + 2000, // Default markup
                    'provider' => 'mock',
                ]
            );
        }
    }

    /**
     * Execute a PPOB transaction
     */
    public function executeTransaction(int $userId, int $branchId, string $productCode, string $customerNumber)
    {
        $product = PpobProduct::where('code', $productCode)->firstOrFail();
        $refId = 'PPOB-' . Str::random(10);

        // 1. Create local transaction record
        $transaction = PpobTransaction::create([
            'user_id' => $userId,
            'branch_id' => $branchId,
            'ppob_product_code' => $productCode,
            'customer_number' => $customerNumber,
            'ref_id' => $refId,
            'amount' => $product->price_sell,
            'status' => 'pending',
        ]);

        // 2. Call Provider API (Mocked)
        // In reality: Http::post('provider_url', [...])
        $success = true; // Simulating success

        if ($success) {
            $transaction->update([
                'status' => 'success',
                'provider_ref_id' => 'PROV-' . rand(1000, 9999),
                'response_msg' => 'Transaksi Berhasil',
            ]);
        } else {
            $transaction->update([
                'status' => 'failed',
                'response_msg' => 'Saldo Provider Tidak Cukup',
            ]);
        }

        return $transaction;
    }
}
