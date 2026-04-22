<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SSOController extends Controller
{
    public function handle(Request $request)
    {
        $dataBase64 = $request->query('data');
        $signature = $request->query('signature');

        if (!$dataBase64 || !$signature) {
            return response('Invalid SSO Request', 400);
        }

        // Decode Data
        $dataJson = base64_decode($dataBase64);
        $payload = json_decode($dataJson, true);

        if (!$payload) {
            return response('Invalid Payload', 400);
        }

        // Validate Expiry (5 minutes - expanded for safety)
        if ((time() * 1000) > $payload['expires']) {
            return response('SSO Token Expired', 403);
        }

        // Verify Signature
        $secret = env('POS_SSO_SECRET', 'your-sso-secret');
        $expectedSignature = hash_hmac('sha256', $dataJson, $secret);

        if ($signature !== $expectedSignature) {
            return response('Invalid Signature', 403);
        }

        // Find or Create User by Phone-based Email
        $email = $payload['phone'] . '@sembok.id';
        \Illuminate\Support\Facades\Log::info('SSO Processing', ['email' => $email]);
        
        $user = User::where('email', $email)->first();
        
        $userData = [
            'name' => $payload['name'],
            'store_name' => $payload['store_name'],
            'store_type' => $payload['store_type'] ?? 'Retail',
            'store_address' => $payload['store_address'] ?? '-',
            'store_phone' => $payload['store_phone'] ?? $payload['phone'],
            'internet_package' => $payload['package'],
            'billing_customer_id' => $payload['customer_id'],
            'email' => $email,
            'is_setup_completed' => true, // Mark as completed since it came from Billing PWA
        ];

        if (!$user) {
            \Illuminate\Support\Facades\Log::info('SSO Creating new user');
            $userData['password'] = Hash::make(\Illuminate\Support\Str::random(24));
            $user = User::create($userData);
        } else {
            \Illuminate\Support\Facades\Log::info('SSO Updating existing user');
            // Update existing user with latest data from Billing
            $user->update($userData);
        }

        // Login explicitly using web guard
        Auth::guard('web')->login($user, true); // true for remember
        session()->regenerate();
        
        \Illuminate\Support\Facades\Log::info('SSO Login Success', [
            'user' => $user->email,
            'session_id' => session()->getId(),
            'auth_check' => Auth::guard('web')->check()
        ]);

        // Check if store setup is completed
        if (!$user->is_setup_completed) {
            return redirect()->route('store.setup');
        }

        // Redirect to Dashboard (Filament)
        return redirect()->to('/admin');
    }
}
