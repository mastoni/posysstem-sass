<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Tokoku - Sembok</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-xl w-full">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-600 rounded-2xl shadow-xl shadow-green-200 mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Selamat Datang di Tokoku!</h1>
            <p class="text-slate-500 mt-2">Lengkapi profil toko Anda untuk mulai berjualan hari ini.</p>
        </div>

        <!-- Setup Card -->
        <div class="glass border border-white shadow-2xl rounded-3xl p-8">
            <form action="{{ route('store.setup.save') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 gap-6">
                    <!-- Nama Toko -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nama Toko</label>
                        <input type="text" name="store_name" value="{{ $user->store_name }}" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none"
                            placeholder="Contoh: Toko Berkah Jaya" required>
                    </div>

                    <!-- Jenis Toko -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Jenis Toko</label>
                        <select name="store_type" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none" required>
                            <option value="">Pilih Jenis Bisnis</option>
                            <option value="Retail">Toko Kelontong / Retail</option>
                            <option value="Cafe">Cafe / Warung Makan</option>
                            <option value="Fashion">Fashion / Pakaian</option>
                            <option value="Jasa">Jasa / Servis</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <!-- Alamat Toko -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Alamat Toko</label>
                        <textarea name="store_address" rows="3" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none"
                            placeholder="Alamat lengkap toko Anda..." required></textarea>
                    </div>

                    <!-- WhatsApp Toko -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">No. WhatsApp Toko</label>
                        <input type="text" name="store_phone" value="{{ auth()->user()->phone ?? '' }}" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all outline-none"
                            placeholder="0812xxxx" required>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" 
                        class="w-full py-4 bg-green-600 hover:bg-green-700 text-white font-bold rounded-2xl shadow-lg shadow-green-200 transition-all active:scale-[0.98]">
                        Aktifkan Toko Saya
                    </button>
                    <p class="text-center text-xs text-slate-400 mt-4 italic">
                        Data ini akan digunakan untuk info pada struk belanja Anda.
                    </p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
