<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pinjamin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="h-full flex font-sans antialiased">

    <!-- Left: Branding (Hidden on mobile) -->
    <div class="hidden lg:flex w-1/2 bg-slate-50 flex-col items-center justify-center p-12 relative overflow-hidden">
        <!-- Decoration -->
        <div class="absolute inset-0 bg-gradient-to-br from-teal-50 to-slate-100 opacity-90"></div>
        <div class="absolute -top-32 -right-32 w-96 h-96 bg-teal-200/50 rounded-full blur-3xl mix-blend-multiply"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-blue-200/50 rounded-full blur-3xl mix-blend-multiply">
        </div>

        <div class="relative z-10 flex flex-col items-center">
            <div class="bg-white p-8 rounded-[3rem] shadow-xl border border-white mb-8">
                <img src="{{ asset('images/pinjamin-logo.png') }}" alt="PINJAMIN Logo" class="h-48 w-auto">
            </div>
            <p class="text-slate-600 font-medium text-lg text-center max-w-sm">Sistem Peminjaman Alat Laboratorium
                Elektro yang Mudah, Cepat, dan Aman.</p>
        </div>
    </div>

    <!-- Right: Form -->
    <div
        class="w-full lg:w-1/2 flex items-center justify-center bg-white p-6 sm:p-12 shadow-[0_0_40px_rgba(0,0,0,0.05)] z-10">
        <div class="w-full max-w-md">

            <!-- Mobile Logo -->
            <div class="flex lg:hidden flex-col items-center mb-8">
                <img src="{{ asset('images/pinjamin-logo.png') }}" alt="PINJAMIN Logo" class="h-24 w-auto mb-2">
            </div>

            <h2 class="text-3xl font-black text-slate-800 mb-2 tracking-tight">Masuk</h2>
            <p class="text-slate-500 mb-8 font-medium">Selamat datang kembali! Silakan masuk ke akun Anda.</p>

            @if(session('success'))
                <div
                    class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-sm font-semibold flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-3 shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div
                    class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-100 text-red-800 text-sm font-semibold flex items-start shadow-sm">
                    <svg class="w-5 h-5 mr-3 mt-0.5 shrink-0 text-red-600" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="/login" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-bold text-slate-700 mb-2">Alamat Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 transition duration-200 text-slate-800 bg-white placeholder-slate-400 font-medium shadow-sm"
                        placeholder="nama@email.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-bold text-slate-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 transition duration-200 text-slate-800 bg-white placeholder-slate-400 font-medium shadow-sm"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                            class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-slate-300 rounded-lg cursor-pointer">
                        <label for="remember" class="ml-2 block text-sm font-medium text-slate-500 cursor-pointer">Ingat
                            saya</label>
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-4 px-4 bg-teal-600 hover:bg-teal-700 active:bg-teal-800 text-white font-black rounded-2xl shadow-lg shadow-teal-600/30 transition-all duration-200 transform hover:-translate-y-0.5">
                    Masuk ke Sistem
                </button>
                
                <div class="relative flex items-center justify-center mt-6 mb-4">
                    <div class="absolute border-t border-slate-200 w-full"></div>
                    <span class="bg-white px-4 text-xs font-bold text-slate-400 uppercase relative z-10">Atau</span>
                </div>

                <a href="{{ route('sso.google') }}"
                    class="w-full py-3.5 px-4 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold rounded-2xl shadow-sm transition-all duration-200 flex items-center justify-center gap-3 group">
                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform text-slate-700" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Masuk dengan SSO
                </a>
            </form>

            <div class="mt-8 pt-8 border-t border-slate-100">
                <p class="text-center text-sm font-medium text-slate-500">
                    Belum punya akun?
                    <a href="/register" class="font-bold text-teal-600 hover:text-teal-700 hover:underline">Daftar
                        sekarang</a>
                </p>
            </div>
        </div>
    </div>

    <x-page-loader />
</body>

</html>