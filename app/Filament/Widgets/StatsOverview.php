<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $userId = $user->id;

        return [
            Stat::make('Bisnis Anda', $user->store_name)
                ->description($user->store_type)
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary'),
            Stat::make('Total Produk', Product::where('user_id', $userId)->count())
                ->description('Jumlah produk di toko Anda')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),
            Stat::make('Total Pesanan', Order::where('user_id', $userId)->count())
                ->description('Total transaksi yang masuk')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
            Stat::make('Total Pendapatan', 'Rp ' . number_format(Order::where('user_id', $userId)->sum('total_amount'), 0, ',', '.'))
                ->description('Total omzet toko Anda')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
    }
}
