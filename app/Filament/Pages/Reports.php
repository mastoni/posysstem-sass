<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class Reports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.reports';

    protected static ?string $title = 'Laporan Penjualan';

    protected static ?string $navigationLabel = 'Laporan Penjualan';

    public $filter = 'today'; // today, week, month, year

    public function getStatsProperty()
    {
        $query = Order::where('user_id', auth()->id())->where('status', 'paid');

        if ($this->filter === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($this->filter === 'week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($this->filter === 'month') {
            $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        }

        return [
            'total_revenue' => $query->sum('total_amount'),
            'total_orders' => $query->count(),
            'avg_order' => $query->avg('total_amount') ?? 0,
        ];
    }

    public function getDailyRevenueProperty()
    {
        return Order::where('user_id', auth()->id())
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    }
}
