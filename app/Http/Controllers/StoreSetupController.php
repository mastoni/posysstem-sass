<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreSetupController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Jika sudah setup, kembalikan ke dashboard
        if ($user->is_setup_completed) {
            return redirect('/admin');
        }

        return view('store-setup', compact('user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:255',
            'store_type' => 'required|string',
            'store_address' => 'required|string',
            'store_phone' => 'required|string|max:20',
        ]);

        $user = Auth::user();
        $user->update([
            'store_name' => $request->store_name,
            'store_type' => $request->store_type,
            'store_address' => $request->store_address,
            'store_phone' => $request->store_phone,
            'is_setup_completed' => true,
        ]);

        return redirect('/admin')->with('success', 'Toko Anda berhasil diaktifkan!');
    }
}
