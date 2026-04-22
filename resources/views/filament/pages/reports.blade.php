<x-filament-panels::page>
    <div class="space-y-8">
        {{-- Filter Header --}}
        <div class="flex items-center justify-between bg-white p-4 rounded-3xl shadow-xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400 pl-4">Periode Laporan</h2>
            <div class="flex gap-2">
                @foreach(['today' => 'Hari Ini', 'week' => 'Minggu Ini', 'month' => 'Bulan Ini'] as $val => $label)
                    <button 
                        wire:click="$set('filter', '{{ $val }}')"
                        class="px-6 py-2 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all {{ $filter === $val ? 'bg-indigo-600 text-white shadow-lg' : 'bg-gray-50 text-gray-500 hover:bg-gray-100' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="bg-gradient-to-br from-indigo-600 to-purple-700 p-8 rounded-[2rem] text-white shadow-2xl relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] opacity-70 mb-2">Total Pendapatan</p>
                <p class="text-3xl font-black tracking-tighter">Rp {{ number_format($this->stats['total_revenue'], 0, ',', '.') }}</p>
                <div class="mt-6 flex items-center gap-2">
                    <x-heroicon-o-banknotes class="w-5 h-5 opacity-50" />
                    <span class="text-[8px] font-bold uppercase tracking-widest opacity-50">Omzet Bersih</span>
                </div>
            </div>

            <div class="bg-white p-8 rounded-[2rem] shadow-xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-400 mb-2">Total Pesanan</p>
                <p class="text-3xl font-black tracking-tighter text-gray-900 dark:text-white">{{ $this->stats['total_orders'] }}</p>
                <div class="mt-6 flex items-center gap-2">
                    <x-heroicon-o-shopping-cart class="w-5 h-5 text-emerald-500" />
                    <span class="text-[8px] font-bold uppercase tracking-widest text-emerald-500">Transaksi Berhasil</span>
                </div>
            </div>

            <div class="bg-white p-8 rounded-[2rem] shadow-xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-500/5 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-400 mb-2">Rata-rata Transaksi</p>
                <p class="text-3xl font-black tracking-tighter text-gray-900 dark:text-white">Rp {{ number_format($this->stats['avg_order'], 0, ',', '.') }}</p>
                <div class="mt-6 flex items-center gap-2">
                    <x-heroicon-o-presentation-chart-line class="w-5 h-5 text-amber-500" />
                    <span class="text-[8px] font-bold uppercase tracking-widest text-amber-500">Average Order Value</span>
                </div>
            </div>
        </div>

        {{-- Chart Placeholder / Table --}}
        <div class="bg-white rounded-[2rem] p-8 shadow-xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
            <div class="mb-8 flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-tight text-gray-900 dark:text-white">Trend Penjualan (30 Hari Terakhir)</h3>
            </div>
            
            <div class="space-y-4">
                @foreach($this->dailyRevenue as $day)
                    <div class="flex items-center gap-6">
                        <div class="w-24 text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</div>
                        <div class="flex-1 h-3 bg-gray-50 rounded-full overflow-hidden dark:bg-gray-800">
                            @php
                                $maxTotal = $this->dailyRevenue->max('total') ?: 1;
                                $width = ($day->total / $maxTotal) * 100;
                            @endphp
                            <div class="h-full bg-indigo-500 rounded-full shadow-lg shadow-indigo-500/20" style="width: {{ $width }}%"></div>
                        </div>
                        <div class="w-32 text-right text-xs font-black text-gray-900 dark:text-white">Rp {{ number_format($day->total, 0, ',', '.') }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>
