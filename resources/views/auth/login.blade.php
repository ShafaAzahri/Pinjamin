<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pinjamin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full flex items-center justify-center font-sans antialiased bg-slate-50 relative overflow-hidden">
    
    <!-- Background Decor -->
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-teal-200/40 rounded-full blur-3xl mix-blend-multiply pointer-events-none"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-blue-200/40 rounded-full blur-3xl mix-blend-multiply pointer-events-none"></div>

    <div class="w-full max-w-md p-6 relative z-10">
        <!-- Brand logo / header -->
        <div class="flex flex-col items-center mb-8">
            <div class="bg-white p-4 sm:p-5 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-white mb-4">
                <img src="{{ asset('images/pinjamin-logo.png') }}" alt="PINJAMIN Logo" class="h-28 w-auto">
            </div>
            <p class="text-sm font-medium text-slate-500 mt-1 text-center">Sistem Peminjaman Alat Laboratorium Elektro</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-slate-100 shadow-xl shadow-slate-100/40 p-8">
            <h2 class="text-2xl font-black text-slate-800 mb-6 text-center tracking-tight">Masuk Akun</h2>

            @if(session('success'))
                <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-sm font-semibold flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-3 shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-100 text-red-800 text-sm font-semibold flex items-start shadow-sm">
                    <svg class="w-5 h-5 mr-3 mt-0.5 shrink-0 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
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
                    <label for="email" class="block text-sm font-bold text-slate-700 mb-2">Alamat Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required 
                        class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 transition duration-200 text-slate-800 bg-slate-50/50 placeholder-slate-400 font-medium"
                        placeholder="nama@email.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-bold text-slate-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" required 
                        class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 transition duration-200 text-slate-800 bg-slate-50/50 placeholder-slate-400 font-medium"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" 
                            class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-slate-300 rounded-lg cursor-pointer">
                        <label for="remember" class="ml-2 block text-sm font-medium text-slate-500 cursor-pointer">Ingat saya</label>
                    </div>
                </div>

                <button type="submit" 
                    class="w-full py-3.5 px-4 bg-teal-600 hover:bg-teal-700 active:bg-teal-800 text-white font-black rounded-2xl shadow-lg shadow-teal-600/30 transition-all duration-200 mt-2">
                    Masuk Sekarang
                </button>
            </form>
        </div>
        
        <p class="text-center text-sm font-medium text-slate-500 mt-8">
            Belum punya akun? 
            <a href="/register" class="font-bold text-teal-600 hover:text-teal-700 hover:underline">Daftar sekarang</a>
        </p>
    </div>
    <x-page-loader />
</body>
</html>
