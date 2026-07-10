<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pinjamin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full flex items-center justify-center font-sans antialiased">
    <div class="w-full max-w-md p-6">
        <!-- Brand logo / header -->
        <div class="flex flex-col items-center mb-8">
            <img src="{{ asset('images/pinjamin-logo.png') }}" alt="PINJAMIN Logo" class="h-32 w-auto mb-2">
            <p class="text-sm text-slate-500 mt-1">Sistem Peminjaman Alat Laboratorium Elektro</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-slate-100 shadow-xl shadow-slate-100/40 p-8">
            <h2 class="text-xl font-bold text-slate-800 mb-6">Masuk ke Akun Anda</h2>

            @if(session('success'))
                <div class="mb-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 rounded-2xl bg-red-50 border border-red-100 text-red-800 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="/login" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Alamat Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required 
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 transition duration-200 text-slate-800 bg-slate-50/50"
                        placeholder="nama@email.com">
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                    </div>
                    <input type="password" id="password" name="password" required 
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 transition duration-200 text-slate-800 bg-slate-50/50"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between py-1">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" 
                            class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-slate-300 rounded-lg">
                        <label for="remember" class="ml-2 block text-sm text-slate-500">Ingat saya</label>
                    </div>
                </div>

                <button type="submit" 
                    class="w-full py-3.5 px-4 bg-teal-700 hover:bg-teal-800 active:scale-[0.98] text-white font-bold rounded-2xl shadow-lg shadow-teal-700/20 transition-all duration-200">
                    Masuk
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-slate-500 mt-8">
            Belum punya akun? 
            <a href="/register" class="font-bold text-teal-700 hover:underline">Daftar sekarang</a>
        </p>
    </div>
    <x-page-loader />
</body>
</html>
