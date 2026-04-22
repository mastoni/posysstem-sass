<x-filament-panels::page>
    <div class="flex flex-col gap-6 lg:flex-row">
        {{-- Products Section --}}
        <div class="flex-1 space-y-6">
            {{-- Search Bar --}}
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400" />
                </div>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Cari produk berdasarkan nama atau SKU..."
                    class="block w-full p-4 pl-12 text-sm text-gray-900 border border-gray-100 rounded-3xl bg-white shadow-xl focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-900 dark:border-gray-800 dark:text-white"
                >
            </div>

            {{-- Products Grid --}}
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-4">
                @foreach($this->products as $product)
                    <button 
                        wire:click="addToCart({{ $product->id }})"
                        class="group relative flex flex-col items-center gap-3 p-4 bg-white rounded-3xl shadow-lg border border-gray-50 transition-all hover:-translate-y-1 hover:shadow-xl active:scale-95 dark:bg-gray-900 dark:border-gray-800"
                    >
                        <div class="relative w-full aspect-square rounded-2xl overflow-hidden bg-gray-50 dark:bg-gray-800">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <x-heroicon-o-photo class="w-12 h-12" />
                                </div>
                            @endif
                            
                            @if($product->stock <= 5)
                                <div class="absolute top-2 right-2 bg-rose-500 text-white text-[8px] font-bold px-2 py-0.5 rounded-full shadow-lg">
                                    Stok: {{ $product->stock }}
                                </div>
                            @endif
                        </div>
                        <div class="text-center">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 truncate w-full">{{ $product->category?->name ?? 'Uncategorized' }}</p>
                            <h3 class="text-xs font-bold text-gray-900 dark:text-white truncate w-full">{{ $product->name }}</h3>
                            <p class="mt-1 text-sm font-black text-amber-600">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Cart Section --}}
        <div class="w-full lg:w-[400px] shrink-0">
            <div class="sticky top-8 bg-white rounded-[2.5rem] shadow-2xl border border-gray-50 overflow-hidden flex flex-col h-[calc(100vh-12rem)] dark:bg-gray-900 dark:border-gray-800">
                {{-- Cart Header --}}
                <div class="p-6 bg-gradient-to-br from-amber-500 to-orange-600 text-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <x-heroicon-o-shopping-cart class="w-6 h-6" />
                            <h2 class="text-lg font-bold">Keranjang Belanja</h2>
                        </div>
                        <span class="bg-white/20 px-3 py-1 rounded-full text-[10px] font-black uppercase backdrop-blur-md">
                            {{ count($cart) }} Item
                        </span>
                    </div>
                </div>

                {{-- Cart Items --}}
                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    @forelse($cart as $id => $item)
                        <div class="flex items-center gap-4 bg-gray-50 p-3 rounded-2xl dark:bg-gray-800 transition-all hover:bg-gray-100 dark:hover:bg-gray-700 group">
                            <div class="w-12 h-12 rounded-xl bg-white overflow-hidden shadow-sm shrink-0">
                                @if($item['image'])
                                    <img src="{{ asset('storage/' . $item['image']) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-200">
                                        <x-heroicon-o-photo class="w-6 h-6" />
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ $item['name'] }}</h4>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button wire:click="updateQuantity({{ $id }}, {{ $item['quantity'] - 1 }})" class="w-6 h-6 rounded-lg bg-white shadow-sm flex items-center justify-center text-gray-400 hover:text-amber-500 transition-colors">
                                    <x-heroicon-o-minus class="w-4 h-4" />
                                </button>
                                <span class="text-xs font-black text-gray-900 dark:text-white w-4 text-center">{{ $item['quantity'] }}</span>
                                <button wire:click="updateQuantity({{ $id }}, {{ $item['quantity'] + 1 }})" class="w-6 h-6 rounded-lg bg-white shadow-sm flex items-center justify-center text-gray-400 hover:text-amber-500 transition-colors">
                                    <x-heroicon-o-plus class="w-4 h-4" />
                                </button>
                            </div>
                            <button wire:click="removeFromCart({{ $id }})" class="text-gray-300 hover:text-rose-500 transition-colors opacity-0 group-hover:opacity-100 p-1">
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </button>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full text-center text-gray-400 py-12">
                            <x-heroicon-o-shopping-bag class="w-16 h-16 opacity-10 mb-4" />
                            <p class="text-sm font-medium italic">Keranjang masih kosong</p>
                        </div>
                    @endforelse
                </div>

                {{-- Cart Footer / Payment --}}
                <div class="p-6 bg-gray-50 border-t border-gray-100 dark:bg-gray-800 dark:border-gray-700 space-y-4">
                    <div class="flex items-center justify-between text-gray-900 dark:text-white">
                        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-400">Total Tagihan</span>
                        <span class="text-2xl font-black tracking-tighter">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Nominal Bayar</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 font-bold">Rp</span>
                            <input 
                                type="number" 
                                wire:model.live="payment_amount" 
                                class="block w-full p-3 pl-12 text-sm font-bold border border-gray-200 rounded-2xl bg-white shadow-inner focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-900 dark:border-gray-800"
                                placeholder="0"
                            >
                        </div>
                    </div>

                    @if($payment_amount > 0)
                        <div class="flex items-center justify-between p-3 bg-amber-50 rounded-2xl dark:bg-amber-900/20 border border-amber-100 dark:border-amber-900/30">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-amber-600">Kembalian</span>
                            <span class="text-lg font-black text-amber-700 dark:text-amber-400">Rp {{ number_format($change, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <button 
                        wire:click="checkout"
                        @if($total <= 0 || $payment_amount < $total) disabled @endif
                        class="w-full bg-amber-500 text-white py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] shadow-lg shadow-amber-500/30 transition-all hover:bg-amber-600 active:scale-95 disabled:opacity-50 disabled:grayscale disabled:pointer-events-none"
                    >
                        Selesaikan Pesanan
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
