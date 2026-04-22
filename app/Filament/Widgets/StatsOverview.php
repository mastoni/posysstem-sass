<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\ReportService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        if ($user->role === 'superadmin') {
            return [
                Stat::make('Total Tenant', User::where('role', 'tenant')->count())
                    ->description('Penyewa aktif')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('success'),
                Stat::make('Total Transaksi PPOB', \App\Models\PpobTransaction::count())
                    ->color('info'),
            ];
        }

        $reportService = new ReportService();
        $summary = $reportService->getProfitLossSummary($user->id);

        return [
            Stat::make('Pendapatan Bulan Ini', 'Rp ' . number_format($summary['revenue'], 0, ',', '.'))
                ->description('Total omzet kotor')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Laba Kotor', 'Rp ' . number_format($summary['profit'], 0, ',', '.'))
                ->description('Omzet - Modal')
                ->descriptionIcon('heroicon-m-presentation-chart-line')
                ->color('info'),
            Stat::make('Total Produk', Product::count())
                ->description('Varian barang')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('warning'),
        ];
    }
}
