<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PosInternalApiController extends Controller
{
    private function authenticate(Request $request)
    {
        $phone = $request->header('X-User-Phone');
        $signature = $request->header('X-Signature');
        $secret = env('POS_SSO_SECRET', 'sembok-pos-secure-key-2026');

        if (!$phone || !$signature) return null;

        $expectedSignature = hash_hmac('sha256', $phone, $secret);
        if (!hash_equals($expectedSignature, $signature)) return null;

        $email = $phone . '@sembok.id';
        
        // Use firstOrCreate to prevent race conditions and handle duplicate entry errors
        try {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $request->header('X-User-Name', 'Pemilik Toko'),
                    'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(24)),
                    'role' => 'tenant',
                    'store_name' => $request->header('X-Store-Name', 'Toko Saya'),
                    'billing_customer_id' => $request->header('X-Customer-ID'),
                    'is_setup_completed' => true
                ]
            );
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Handle race condition: if created by another request, just fetch it
            $user = User::where('email', $email)->first();
        }

        // IMPORTANT: Login the user so Global Scopes can detect the tenant
        auth()->login($user);

        return $user;
    }

    private function resolveBranchId(Request $request, $user)
    {
        $branchId = $user->role === 'cashier' ? $user->branch_id : $request->input('branch_id');
        
        if (!$branchId && $user->role === 'tenant') {
            $branch = \App\Models\Branch::where('user_id', $user->id)->first();
            if (!$branch) {
                $branch = \App\Models\Branch::create([
                    'user_id' => $user->id,
                    'name' => 'Cabang Utama',
                    'is_main' => true,
                    'is_active' => true,
                    'address' => $user->store_address ?? 'Alamat belum diatur'
                ]);
            }
            $branchId = $branch->id;
        }
        
        return $branchId;
    }

    public function updateStore(Request $request)
    {
        $user = $this->authenticate($request);
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $updateData = [
            'name' => $request->name ?? $user->name,
            'store_name' => $request->store_name ?? $user->store_name,
            'store_type' => $request->store_type ?? $user->store_type,
            'store_address' => $request->store_address ?? $user->store_address,
            'store_phone' => $request->store_phone ?? $user->store_phone,
            'is_ppob_enabled' => $request->has('is_ppob_enabled') ? $request->is_ppob_enabled : $user->is_ppob_enabled,
        ];

        // If phone changed, update both phone and derived email
        if ($request->store_phone && $request->store_phone !== $user->phone) {
            $updateData['phone'] = $request->store_phone;
            $updateData['email'] = $request->store_phone . '@sembok.id';
        }

        $user->update($updateData);

        return response()->json(['success' => true, 'message' => 'Profil toko diperbarui']);
    }

    public function getProducts(Request $request)
    {
        $user = $this->authenticate($request);
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $products = Product::where('user_id', $user->id)->with('category')->get();
        return response()->json(['success' => true, 'data' => $products]);
    }

    public function storeProduct(Request $request)
    {
        $user = $this->authenticate($request);
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $categoryId = $request->category_id;
        if (!$categoryId) {
            $defaultCategory = \App\Models\Category::firstOrCreate(
                ['user_id' => $user->id, 'name' => 'Umum'],
                ['description' => 'Kategori Bawaan']
            );
            $categoryId = $defaultCategory->id;
        }

        $imagePath = $this->handleBase64Image($request->image, $user->id);

        $product = \App\Models\Product::create([
            'user_id' => $user->id,
            'category_id' => $categoryId,
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name) . '-' . time(),
            'price' => $request->price,
            'cost_price' => $request->cost_price ?? 0,
            'stock' => $request->stock ?? 0,
            'description' => $request->description,
            'image' => $imagePath,
            'sku' => $request->sku ?? ('P' . time()),
            'is_active' => true
        ]);

        return response()->json(['success' => true, 'data' => $product]);
    }

    public function updateProduct(Request $request, $id)
    {
        $user = $this->authenticate($request);
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $product = \App\Models\Product::where('user_id', $user->id)->findOrFail($id);
        
        $updateData = $request->only(['category_id', 'name', 'price', 'cost_price', 'stock', 'description', 'sku', 'is_active']);
        
        if ($request->image) {
            $updateData['image'] = $this->handleBase64Image($request->image, $user->id);
        }

        if ($request->name && $request->name !== $product->name) {
            $updateData['slug'] = \Illuminate\Support\Str::slug($request->name) . '-' . time();
        }
        
        $product->update($updateData);

        return response()->json(['success' => true, 'data' => $product]);
    }

    public function deleteProduct(Request $request, $id)
    {
        $user = $this->authenticate($request);
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $product = Product::where('user_id', $user->id)->findOrFail($id);
        $product->delete();

        return response()->json(['success' => true, 'message' => 'Produk dihapus']);
    }

    public function getCategories(Request $request)
    {
        $user = $this->authenticate($request);
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $categories = Category::where('user_id', $user->id)->get();
        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function storeCategory(Request $request)
    {
        $user = $this->authenticate($request);
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $category = Category::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->name) . '-' . time(),
            'description' => $request->description
        ]);

        return response()->json(['success' => true, 'data' => $category]);
    }

    public function updateCategory(Request $request, $id)
    {
        $user = $this->authenticate($request);
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $category = Category::where('user_id', $user->id)->findOrFail($id);
        
        $updateData = $request->only(['name', 'description']);
        if ($request->name && $request->name !== $category->name) {
            $updateData['slug'] = \Illuminate\Support\Str::slug($request->name) . '-' . time();
        }
        
        $category->update($updateData);

        return response()->json(['success' => true, 'data' => $category]);
    }

    public function deleteCategory(Request $request, $id)
    {
        $user = $this->authenticate($request);
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $category = Category::where('user_id', $user->id)->findOrFail($id);
        $category->delete();

        return response()->json(['success' => true, 'message' => 'Kategori dihapus']);
    }

    public function checkout(Request $request)
    {
        $user = $this->authenticate($request);
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $items = $request->input('items');
        $total = $request->input('total');
        $paymentMethod = $request->input('payment_method', 'Cash');
        
        // Identify Branch: from Cashier's assigned branch or request (for Owner)
        $branchId = $this->resolveBranchId($request, $user);

        if (!$branchId) {
            return response()->json(['success' => false, 'message' => 'Cabang tidak teridentifikasi'], 400);
        }

        try {
            DB::beginTransaction();

            $order = Order::create([
                'user_id' => $user->role === 'cashier' ? $user->owner_id : $user->id,
                'branch_id' => $branchId,
                'order_number' => 'TRX-' . strtoupper(\Illuminate\Support\Str::random(8)),
                'total_amount' => $total,
                'payment_method' => $paymentMethod,
                'status' => 'paid',
            ]);

            foreach ($items as $item) {
                $product = Product::where('user_id', $user->role === 'cashier' ? $user->owner_id : $user->id)
                    ->find($item['id']);

                if (!$product) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan: ID ' . $item['id']], 400);
                }

                // Try FIFO batch reduction first, fall back to direct product stock
                $hasBatches = \App\Models\StockBatch::where('product_id', $item['id'])
                    ->where('quantity_remaining', '>', 0)
                    ->exists();

                if ($hasBatches) {
                    $inventoryService = new \App\Services\InventoryService();
                    $batchesUsed = $inventoryService->reduceStock($item['id'], $branchId, $item['quantity']);

                    foreach ($batchesUsed as $batchData) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $item['id'],
                            'quantity' => $batchData['quantity'],
                            'price' => $item['price'],
                            'subtotal' => $item['price'] * $batchData['quantity'],
                            'stock_batch_id' => $batchData['batch_id'],
                            'cost_price' => $batchData['cost_price'],
                        ]);
                    }

                    // If still items left after batches, create remaining as one item
                    $batchedQty = array_sum(array_column($batchesUsed, 'quantity'));
                    if ($batchedQty < $item['quantity']) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $item['id'],
                            'quantity' => $item['quantity'] - $batchedQty,
                            'price' => $item['price'],
                            'subtotal' => $item['price'] * ($item['quantity'] - $batchedQty),
                        ]);
                    }
                } else {
                    // Simple mode: direct stock decrement on products table
                    if ($product->stock < $item['quantity']) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'Stok tidak mencukupi untuk: ' . $product->name], 400);
                    }

                    $product->decrement('stock', $item['quantity']);

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['price'] * $item['quantity'],
                        'cost_price' => $product->cost_price ?? 0,
                    ]);
                }
            }

            DB::commit();
            
            $order->load('items.product');
            
            return response()->json([
                'success' => true, 
                'order_number' => $order->order_number,
                'order' => $order
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getOrders(Request $request)
    {
        $user = $this->authenticate($request);
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $orders = Order::where('user_id', $user->id)
            ->with(['items.product'])
            ->latest()
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function cancelOrder(Request $request, $id)
    {
        $user = $this->authenticate($request);
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $reason      = $request->input('reason', 'Tidak ada alasan');
        $returnType  = $request->input('return_type', 'full'); // 'full' or 'partial'
        $isReturn    = $request->boolean('is_return', false);  // true = retur pasca-jual, false = batal biasa

        try {
            DB::beginTransaction();

            $order = Order::where('user_id', $user->id)
                ->with(['items.product', 'items.stockBatch'])
                ->findOrFail($id);

            if (in_array($order->status, ['cancelled', 'returned'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan sudah ' . ($order->status === 'returned' ? 'diretur' : 'dibatalkan')
                ], 400);
            }

            // Restore stock for each item
            $restoredItems = [];
            foreach ($order->items as $item) {
                if ($item->stock_batch_id && $item->stockBatch) {
                    $item->stockBatch->increment('quantity', $item->quantity);
                } else {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                        $restoredItems[] = [
                            'product'  => $item->product->name,
                            'quantity' => $item->quantity,
                        ];
                    }
                }
            }

            $newStatus = $isReturn ? 'returned' : 'cancelled';

            $order->update([
                'status'        => $newStatus,
                'cancel_reason' => $reason,
                'cancelled_at'  => now(),
                'return_type'   => $returnType,
                'notes'         => ($order->notes ? $order->notes . "\n" : '') 
                                  . "[{$newStatus}] " . now()->format('d/m/Y H:i') . " - {$reason}",
            ]);

            DB::commit();

            $label = $isReturn ? 'Retur' : 'Pembatalan';
            return response()->json([
                'success'        => true,
                'message'        => "{$label} berhasil. Stok dikembalikan.",
                'status'         => $newStatus,
                'reason'         => $reason,
                'restored_items' => $restoredItems,
                'cancelled_at'   => now()->toDateTimeString(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function getFullReports(Request $request)
    {
        $user = $this->authenticate($request);
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $stats = [
            'total_orders' => \App\Models\Order::where('user_id', $user->id)->count(),
            'total_sales'  => \App\Models\Order::where('user_id', $user->id)->sum('total_amount'),
            'today_orders' => \App\Models\Order::where('user_id', $user->id)
                ->whereDate('created_at', now()->toDateString())
                ->count(),
            'today_sales'  => \App\Models\Order::where('user_id', $user->id)
                ->whereDate('created_at', now()->toDateString())
                ->sum('total_amount'),
            'recent_orders' => \App\Models\Order::where('user_id', $user->id)->latest()->take(10)->get(),
            'daily_trend' => \App\Models\Order::where('user_id', $user->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
                ->groupBy('date')
                ->get(),
            'profit_loss' => [
                'gross_profit' => \App\Models\Order::where('user_id', $user->id)->sum('total_amount') - 
                                \App\Models\OrderItem::whereHas('order', fn($q) => $q->where('user_id', $user->id))
                                ->join('products', 'order_items.product_id', '=', 'products.id')
                                ->sum(DB::raw('order_items.quantity * products.cost_price'))
            ]
        ];

        return response()->json(['success' => true, 'data' => $stats]);
    }

    public function getPpobProducts(Request $request)
    {
        $products = \App\Models\PpobProduct::where('is_active', true)
            ->get()
            ->groupBy('category');

        return response()->json(['success' => true, 'data' => $products]);
    }

    public function buyPpob(Request $request)
    {
        $user = $this->authenticate($request);
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $productCode = $request->input('product_code');
        $customerNumber = $request->input('customer_number');
        $branchId = $this->resolveBranchId($request, $user);

        try {
            $ppobService = new \App\Services\PpobService();
            $transaction = $ppobService->executeTransaction($user->id, $branchId, $productCode, $customerNumber);

            return response()->json([
                'success' => $transaction->status === 'success',
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function handleBase64Image($base64String, $userId)
    {
        if (!$base64String || !str_starts_with($base64String, 'data:image')) {
            return $base64String;
        }

        try {
            $format = explode('/', explode(':', substr($base64String, 0, strpos($base64String, ';')))[1])[1];
            $image = str_replace(' ', '+', explode(',', $base64String)[1]);
            $imageName = 'product_' . $userId . '_' . time() . '.' . $format;
            
            \Illuminate\Support\Facades\Storage::disk('public')->put($imageName, base64_decode($image));
            
            return $imageName;
        } catch (\Exception $e) {
            return null;
        }
    }
}
