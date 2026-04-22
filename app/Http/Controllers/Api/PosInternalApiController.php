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
        if ($signature !== $expectedSignature) return null;

        $email = $phone . '@sembok.id';
        return User::where('email', $email)->first();
    }

    public function getProducts(Request $request)
    {
        $user = $this->authenticate($request);
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $products = Product::where('user_id', $user->id)
            ->with('category')
            ->where('is_active', true)
            ->get();

        return response()->json(['success' => true, 'data' => $products]);
    }

    public function getCategories(Request $request)
    {
        $user = $this->authenticate($request);
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $categories = Category::where('user_id', $user->id)->get();
        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function checkout(Request $request)
    {
        $user = $this->authenticate($request);
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $items = $request->input('items');
        $total = $request->input('total');

        try {
            DB::beginTransaction();

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'TRX-' . strtoupper(str()->random(8)),
                'total_amount' => $total,
                'payment_method' => 'Cash',
                'status' => 'paid',
            ]);

            foreach ($items as $item) {
                $product = Product::find($item['id']);
                if ($product && $product->stock >= $item['quantity']) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                    $product->decrement('stock', $item['quantity']);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'order_number' => $order->order_number]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getFullReports(Request $request)
    {
        $user = $this->authenticate($request);
        if (!$user) return response()->json(['error' => 'Unauthorized'], 401);

        $stats = [
            'today' => Order::where('user_id', $user->id)->whereDate('created_at', today())->sum('total_amount'),
            'this_month' => Order::where('user_id', $user->id)->whereMonth('created_at', now()->month)->sum('total_amount'),
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'recent_orders' => Order::where('user_id', $user->id)->latest()->limit(10)->get(),
            'daily_trend' => Order::where('user_id', $user->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
                ->groupBy('date')
                ->get()
        ];

        return response()->json(['success' => true, 'data' => $stats]);
    }
}
