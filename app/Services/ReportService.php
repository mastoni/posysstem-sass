<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PpobTransaction;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get profit and loss summary for a tenant/branch.
     */
    public function getProfitLossSummary($userId, $branchId = null, $startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfDay();

        // 1. Physical Products Revenue & Profit
        $productStats = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.user_id', $userId)
            ->when($branchId, fn($q) => $q->where('orders.branch_id', $branchId))
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', 'paid')
            ->select(
                DB::raw('SUM(order_items.quantity * order_items.price) as revenue'),
                DB::raw('SUM(order_items.quantity * order_items.cost_price) as cogs')
            )
            ->first();

        // 2. PPOB Revenue & Profit
        // Profit PPOB = Sell Price - Buy Price (from ppob_products)
        // Note: For simplicity, we assume price_sell - price_buy from the transaction date
        $ppobStats = DB::table('ppob_transactions')
            ->join('ppob_products', 'ppob_transactions.ppob_product_code', '=', 'ppob_products.code')
            ->where('ppob_transactions.user_id', $userId)
            ->when($branchId, fn($q) => $q->where('ppob_transactions.branch_id', $branchId))
            ->whereBetween('ppob_transactions.created_at', [$startDate, $endDate])
            ->where('ppob_transactions.status', 'success')
            ->select(
                DB::raw('SUM(ppob_transactions.amount) as revenue'),
                DB::raw('SUM(ppob_products.price_buy) as cogs')
            )
            ->first();

        $totalRevenue = ($productStats->revenue ?? 0) + ($ppobStats->revenue ?? 0);
        $totalCogs = ($productStats->cogs ?? 0) + ($ppobStats->cogs ?? 0);
        $grossProfit = $totalRevenue - $totalCogs;

        return [
            'revenue' => $totalRevenue,
            'cogs' => $totalCogs,
            'profit' => $grossProfit,
            'details' => [
                'physical' => [
                    'revenue' => $productStats->revenue ?? 0,
                    'cogs' => $productStats->cogs ?? 0,
                    'profit' => ($productStats->revenue ?? 0) - ($productStats->cogs ?? 0),
                ],
                'ppob' => [
                    'revenue' => $ppobStats->revenue ?? 0,
                    'cogs' => $ppobStats->cogs ?? 0,
                    'profit' => ($ppobStats->revenue ?? 0) - ($ppobStats->cogs ?? 0),
                ]
            ]
        ];
    }
}
