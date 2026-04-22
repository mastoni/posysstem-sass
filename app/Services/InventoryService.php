<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockBatch;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Add stock to a branch using a new batch.
     */
    public function addStock(int $productId, int $branchId, int $quantity, float $costPrice, ?string $batchNumber = null, ?string $expiredAt = null)
    {
        return DB::transaction(function () use ($productId, $branchId, $quantity, $costPrice, $batchNumber, $expiredAt) {
            // 1. Create the batch
            $batch = StockBatch::create([
                'product_id' => $productId,
                'branch_id' => $branchId,
                'quantity_initial' => $quantity,
                'quantity_remaining' => $quantity,
                'cost_price' => $costPrice,
                'batch_number' => $batchNumber,
                'expired_at' => $expiredAt,
            ]);

            // 2. Update the product_branches pivot total stock
            $product = Product::find($productId);
            $branchStock = $product->branches()->where('branch_id', $branchId)->first();

            if ($branchStock) {
                $product->branches()->updateExistingPivot($branchId, [
                    'stock' => $branchStock->pivot->stock + $quantity
                ]);
            } else {
                $product->branches()->attach($branchId, [
                    'stock' => $quantity,
                    'price' => $product->price // Default to main product price
                ]);
            }

            return $batch;
        });
    }

    /**
     * Reduce stock using FIFO logic.
     * Returns an array of batches used and their quantities.
     */
    public function reduceStock(int $productId, int $branchId, int $quantityToReduce)
    {
        return DB::transaction(function () use ($productId, $branchId, $quantityToReduce) {
            $batchesUsed = [];
            $remainingToReduce = $quantityToReduce;

            // 1. Get available batches ordered by oldest first (FIFO)
            $batches = StockBatch::where('product_id', $productId)
                ->where('branch_id', $branchId)
                ->where('quantity_remaining', '>', 0)
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($batches as $batch) {
                if ($remainingToReduce <= 0) break;

                $takeFromThisBatch = min($batch->quantity_remaining, $remainingToReduce);
                
                $batch->decrement('quantity_remaining', $takeFromThisBatch);
                $remainingToReduce -= $takeFromThisBatch;

                $batchesUsed[] = [
                    'batch_id' => $batch->id,
                    'quantity' => $takeFromThisBatch,
                    'cost_price' => $batch->cost_price
                ];
            }

            if ($remainingToReduce > 0) {
                // Potential issue: Overselling / Stock mismatch
                // In a strict FIFO, you might want to throw an exception here
                // throw new \Exception("Insufficient stock for product ID {$productId} in branch {$branchId}");
            }

            // 2. Update total stock in product_branches
            $product = Product::find($productId);
            $product->branches()->where('branch_id', $branchId)->decrement('stock', $quantityToReduce);

            return $batchesUsed;
        });
    }

    /**
     * Transfer stock between branches.
     */
    public function transferStock(int $productId, int $fromBranchId, int $toBranchId, int $quantity)
    {
        return DB::transaction(function () use ($productId, $fromBranchId, $toBranchId, $quantity) {
            // 1. Reduce from source (FIFO)
            $batchesUsed = $this->reduceStock($productId, $fromBranchId, $quantity);
            
            // 2. Add to destination (Using the same cost prices from source batches)
            foreach ($batchesUsed as $batchData) {
                $this->addStock(
                    $productId, 
                    $toBranchId, 
                    $batchData['quantity'], 
                    $batchData['cost_price'], 
                    'TRANSFER-FROM-BR-' . $fromBranchId
                );
            }

            return true;
        });
    }
}
