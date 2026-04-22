<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class ExternalStatsController extends Controller
{
    public function getStats(Request $request)
    {
        $phone = $request->query('phone');
        $signature = $request->query('signature');
        $secret = env('POS_SSO_SECRET', 'sembok-pos-secure-key-2026');

        // Simple security check
        $expectedSignature = hash_hmac('sha256', $phone, $secret);
        if ($signature !== $expectedSignature) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $email = $phone . '@sembok.id';
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => true,
                'data' => [
                    'active' => false,
                    'message' => 'Belum diaktivasi'
                ]
            ]);
        }

        $userId = $user->id;

        return response()->json([
            'success' => true,
            'data' => [
                'active' => true,
                'store_name' => $user->store_name,
                'total_products' => Product::where('user_id', $userId)->count(),
                'total_orders' => Order::where('user_id', $userId)->count(),
                'monthly_revenue' => Order::where('user_id', $userId)
                    ->whereMonth('created_at', now()->month)
                    ->sum('total_amount'),
                'last_order' => Order::where('user_id', $userId)
                    ->latest()
                    ->first()?->order_number ?? '-',
            ]
        ]);
    }
}
