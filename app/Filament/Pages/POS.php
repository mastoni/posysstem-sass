<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class POS extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string $view = 'filament.pages.p-o-s';

    protected static ?string $title = 'Kasir POS';

    protected static ?string $navigationLabel = 'Kasir POS';

    public $search = '';
    public $cart = [];
    public $total = 0;
    public $payment_amount = 0;
    public $change = 0;

    public function mount()
    {
        $this->cart = [];
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        
        if (!$product || $product->stock <= 0) {
            Notification::make()
                ->title('Stok Habis')
                ->danger()
                ->send();
            return;
        }

        if (isset($this->cart[$productId])) {
            if ($this->cart[$productId]['quantity'] + 1 > $product->stock) {
                Notification::make()
                    ->title('Stok Tidak Mencukupi')
                    ->warning()
                    ->send();
                return;
            }
            $this->cart[$productId]['quantity']++;
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'image' => $product->image,
            ];
        }

        $this->calculateTotal();
    }

    public function removeFromCart($productId)
    {
        unset($this->cart[$productId]);
        $this->calculateTotal();
    }

    public function updateQuantity($productId, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeFromCart($productId);
            return;
        }

        $product = Product::find($productId);
        if ($quantity > $product->stock) {
            $this->cart[$productId]['quantity'] = $product->stock;
            Notification::make()
                ->title('Maksimal Stok')
                ->warning()
                ->send();
        } else {
            $this->cart[$productId]['quantity'] = $quantity;
        }

        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total = collect($this->cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });
        $this->updatedPaymentAmount();
    }

    public function updatedPaymentAmount()
    {
        $this->change = max(0, $this->payment_amount - $this->total);
    }

    public function checkout()
    {
        if (empty($this->cart)) {
            Notification::make()
                ->title('Keranjang Kosong')
                ->danger()
                ->send();
            return;
        }

        if ($this->payment_amount < $this->total) {
            Notification::make()
                ->title('Pembayaran Kurang')
                ->danger()
                ->send();
            return;
        }

        DB::transaction(function () {
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => 'TRX-' . strtoupper(str()->random(8)),
                'total_amount' => $this->total,
                'payment_method' => 'Cash',
                'status' => 'paid',
            ]);

            foreach ($this->cart as $item) {
                $product = Product::find($item['id']);
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                // Update stock
                $product->decrement('stock', $item['quantity']);
            }
        });

        Notification::make()
            ->title('Transaksi Berhasil')
            ->success()
            ->send();

        $this->cart = [];
        $this->total = 0;
        $this->payment_amount = 0;
        $this->change = 0;
        $this->search = '';
    }

    public function getProductsProperty()
    {
        return Product::where('user_id', auth()->id())
            ->where('is_active', true)
            ->where('name', 'like', '%' . $this->search . '%')
            ->limit(12)
            ->get();
    }
}
