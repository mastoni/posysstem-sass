<x-filament-panels::page>
    <div class="space-y-8">
        {{-- Custom Dashboard Header - Premium Style --}}
        <div
            class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-indigo-600 via-indigo-700 to-purple-800 p-8 text-white shadow-2xl">
            <div class="absolute -right-10 -top-10 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-purple-500/20 blur-3xl"></div>

            <div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-6">
                    <div class="relative h-20 w-20 shrink-0">
                        <div class="absolute inset-0 rotate-6 rounded-3xl bg-white/20 backdrop-blur-md"></div>
                        <div
                            class="relative flex h-full w-full items-center justify-center rounded-3xl border-2 border-white/30 bg-white/10 shadow-xl backdrop-blur-xl">
                            <x-heroicon-o-building-storefront class="h-10 w-10 text-white" />
                        </div>
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-tight">{{ auth()->user()->store_name }}</h1>
                        <div class="mt-1 flex items-center gap-2">
                            <span
                                class="rounded-full bg-white/20 px-3 py-0.5 text-[10px] font-bold uppercase tracking-widest backdrop-blur-md">{{ auth()->user()->store_type }}</span>
                            <span class="text-xs font-medium text-indigo-100/80">ID: {{ auth()->user()->id }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-4">
                    <a href="http://localhost:3000/app/dashboard"
                        class="flex items-center gap-2 rounded-2xl border border-white/30 bg-white/20 px-6 py-3 text-[10px] font-bold uppercase tracking-widest shadow-xl backdrop-blur-md transition-all hover:bg-white/30 active:scale-95">
                        <x-heroicon-o-arrow-left class="h-4 w-4" />
                        Kembali ke Billing
                    </a>
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-4 shadow-inner backdrop-blur-md">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-indigo-100">Total Produk</p>
                        <p class="text-2xl font-black tracking-tighter">
                            {{ \App\Models\Product::where('user_id', auth()->id())->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/20 bg-white/10 p-4 shadow-inner backdrop-blur-md">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-indigo-100">Omzet Hari Ini</p>
                        <p class="text-2xl font-black tracking-tighter">Rp
                            {{ number_format(\App\Models\Order::where('user_id', auth()->id())->whereDate('created_at', today())->sum('total_amount'), 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions - Grid Menu --}}
        <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
            @php
                $menus = [
                    ['name' => 'Kasir POS', 'icon' => 'heroicon-o-shopping-cart', 'color' => 'bg-amber-500', 'href' => \App\Filament\Pages\POS::getUrl()],
                    ['name' => 'Produk', 'icon' => 'heroicon-o-archive-box', 'color' => 'bg-blue-500', 'href' => \App\Filament\Resources\Products\ProductResource::getUrl('index')],
                    ['name' => 'Kategori', 'icon' => 'heroicon-o-tag', 'color' => 'bg-emerald-500', 'href' => \App\Filament\Resources\Categories\CategoryResource::getUrl('index')],
                    ['name' => 'Laporan', 'icon' => 'heroicon-o-chart-bar', 'color' => 'bg-rose-500', 'href' => \App\Filament\Pages\Reports::getUrl()],
                ];
            @endphp

            @foreach($menus as $menu)
                <a href="{{ $menu['href'] }}"
                    class="group relative flex flex-col items-center gap-4 rounded-[2rem] bg-white p-6 shadow-xl transition-all hover:-translate-y-1 hover:shadow-2xl active:scale-95 dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
                    <div
                        class="{{ $menu['color'] }} flex h-16 w-16 items-center justify-center rounded-2xl text-white shadow-lg shadow-{{ explode('-', $menu['color'])[1] }}-500/30 transition-transform group-hover:scale-110 group-hover:rotate-3">
                        @svg($menu['icon'], 'w-8 h-8')
                    </div>
                    <span
                        class="text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white">{{ $menu['name'] }}</span>
                </a>
            @endforeach
        </div>

        {{-- Bottom Stats Section --}}
        <div class="grid gap-6 md:grid-cols-2">
            {{-- Recent Orders Card --}}
            <div
                class="rounded-[2rem] bg-white p-8 shadow-xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-tight text-gray-900 dark:text-white">Pesanan
                        Terakhir</h3>
                    <a href="#"
                        class="text-[10px] font-bold uppercase tracking-widest text-indigo-600 hover:text-indigo-500 transition-colors">Lihat
                        Semua</a>
                </div>

                <div class="space-y-4">
                    @php
                        $recentOrders = \App\Models\Order::where('user_id', auth()->id())->latest()->limit(4)->get();
                    @endphp

                    @forelse($recentOrders as $order)
                        <div
                            class="flex items-center justify-between rounded-2xl bg-gray-50 p-4 transition-colors hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700">
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-indigo-600 shadow-sm dark:bg-gray-900">
                                    <x-heroicon-o-receipt-percent class="h-6 w-6" />
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-900 dark:text-white">{{ $order->order_number }}
                                    </p>
                                    <p class="text-[10px] text-gray-400">{{ $order->created_at->format('d M Y, H:i') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold text-gray-900 dark:text-white">Rp
                                    {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                                <span
                                    class="text-[8px] font-bold uppercase tracking-widest {{ $order->status === 'paid' ? 'text-emerald-500' : 'text-amber-500' }}">
                                    {{ $order->status }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-8 text-center text-gray-400">
                            <x-heroicon-o-shopping-bag class="mb-2 h-12 w-12 opacity-20" />
                            <p class="text-[10px] italic">Belum ada transaksi</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Store Info Card --}}
            <div
                class="rounded-[2rem] bg-white p-8 shadow-xl dark:bg-gray-900 border border-gray-100 dark:border-gray-800 relative overflow-hidden">
                <div
                    class="absolute top-0 right-0 h-32 w-32 bg-gray-50 dark:bg-gray-800 rounded-full blur-3xl -mr-16 -mt-16 opacity-50">
                </div>

                <h3 class="relative z-10 mb-6 text-sm font-bold uppercase tracking-tight text-gray-900 dark:text-white">
                    Profil Toko</h3>

                <div class="relative z-10 space-y-6">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30">
                            <x-heroicon-o-map-pin class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Alamat Toko</p>
                            <p class="text-xs font-medium text-gray-900 dark:text-white">
                                {{ auth()->user()->store_address }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30">
                            <x-heroicon-o-phone class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Nomor Telepon</p>
                            <p class="text-xs font-medium text-gray-900 dark:text-white">
                                {{ auth()->user()->store_phone }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-900/30">
                            <x-heroicon-o-cube class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Paket Internet</p>
                            <p class="text-xs font-medium text-gray-900 dark:text-white">
                                {{ auth()->user()->internet_package }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>