<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') - Pinjamin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full flex flex-col font-sans antialiased bg-slate-50">

    <!-- Navigation Bar -->
    <nav class="bg-white/80 backdrop-blur-md border-b border-slate-100 sticky top-0 z-40 shadow-sm">
        <div class="w-full px-4 sm:px-8 lg:px-12 xl:px-20">
            <div class="flex justify-between h-16 items-center">
                <!-- Brand -->
                <a href="{{ route('student.catalog') }}" class="flex items-center">
                    <img src="{{ asset('images/pinjamin-logo.png') }}" alt="PINJAMIN Logo" class="h-14 w-auto">
                </a>

                <!-- Nav Links -->
                <div class="hidden sm:flex items-center space-x-1">
                    <a href="{{ route('student.catalog') }}" 
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ request()->is('catalog*') ? 'bg-teal-50 text-teal-700' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
                        Katalog
                    </a>
                    <a href="{{ route('student.cart') }}" 
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition relative {{ request()->is('cart*') ? 'bg-teal-50 text-teal-700' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
                        Keranjang
                        @if(count(session('cart', [])) > 0)
                            <span class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow-sm">
                                {{ count(session('cart', [])) }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('student.loans') }}" 
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition relative {{ request()->is('loans*') ? 'bg-teal-50 text-teal-700' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
                        Peminjaman
                        @php
                            $activeLoansCount = \App\Models\Loan::where('user_id', Auth::id())->whereNotIn('status', ['selesai', 'ditolak'])->count();
                        @endphp
                        @if($activeLoansCount > 0)
                            <span class="absolute -top-1 -right-1 h-5 w-5 bg-teal-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow-sm">
                                {{ $activeLoansCount }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('student.fines') }}" 
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition relative {{ request()->is('fines*') ? 'bg-teal-50 text-teal-700' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
                        Denda
                        @php
                            $unpaidFinesCount = \App\Models\Fine::whereHas('loan', function($q) { $q->where('user_id', Auth::id()); })->whereIn('status', ['belum_lunas', 'menunggu_verifikasi'])->count();
                        @endphp
                        @if($unpaidFinesCount > 0)
                            <span class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow-sm">
                                {{ $unpaidFinesCount }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('student.notifications') }}" 
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition relative {{ request()->is('notifications*') ? 'bg-teal-50 text-teal-700' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @php
                            $unreadCount = \App\Models\Notification::where('user_id', Auth::id())->where('is_read', false)->count();
                        @endphp
                        @if($unreadCount > 0)
                            <span class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow-sm">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </a>
                </div>

                <!-- User Menu -->
                <div class="flex items-center space-x-3" x-data="{ open: false }">
                    <div class="relative">
                        <button @click="open = !open" class="flex items-center space-x-2 px-3 py-2 rounded-xl hover:bg-slate-50 transition">
                            <div class="h-8 w-8 rounded-full bg-teal-100 flex items-center justify-center text-teal-700 font-bold text-sm uppercase">
                                {{ substr(Auth::user()->name, 0, 2) }}
                            </div>
                            <span class="text-sm font-semibold text-slate-700 hidden sm:block">{{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition
                            class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50">
                            <div class="px-4 py-2 border-b border-slate-50">
                                <p class="text-sm font-bold text-slate-800">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-slate-400">{{ Auth::user()->nim }}</p>
                            </div>
                            <a href="{{ route('student.profile') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 font-semibold transition">
                                Profil Saya
                            </a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-semibold transition">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Nav -->
        <div class="sm:hidden border-t border-slate-100 px-4 py-2 flex justify-around">
            <a href="{{ route('student.catalog') }}" class="p-2 {{ request()->is('catalog*') ? 'text-teal-600' : 'text-slate-400' }}">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span class="text-[10px] block text-center mt-0.5 font-semibold">Katalog</span>
            </a>
            <a href="{{ route('student.cart') }}" class="p-2 relative {{ request()->is('cart*') ? 'text-teal-600' : 'text-slate-400' }}">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                </svg>
                <span class="text-[10px] block text-center mt-0.5 font-semibold">Keranjang</span>
            </a>
            <a href="{{ route('student.loans') }}" class="p-2 relative {{ request()->is('loans*') ? 'text-teal-600' : 'text-slate-400' }}">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span class="text-[10px] block text-center mt-0.5 font-semibold">Pinjaman</span>
                @if($activeLoansCount > 0)
                    <span class="absolute top-1 right-2 h-4 w-4 bg-teal-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center shadow-sm border border-white">
                        {{ $activeLoansCount }}
                    </span>
                @endif
            </a>
            <a href="{{ route('student.fines') }}" class="p-2 relative {{ request()->is('fines*') ? 'text-teal-600' : 'text-slate-400' }}">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-[10px] block text-center mt-0.5 font-semibold">Denda</span>
                @if($unpaidFinesCount > 0)
                    <span class="absolute top-1 right-2 h-4 w-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center shadow-sm border border-white">
                        {{ $unpaidFinesCount }}
                    </span>
                @endif
            </a>
            <a href="{{ route('student.notifications') }}" class="p-2 relative {{ request()->is('notifications*') ? 'text-teal-600' : 'text-slate-400' }}">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span class="text-[10px] block text-center mt-0.5 font-semibold">Notif</span>
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1">
        <div class="w-full px-4 sm:px-8 lg:px-12 xl:px-20 py-8">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-2xl flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-800 rounded-2xl flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-3 text-red-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-100 py-4">
        <div class="w-full px-4 sm:px-8 lg:px-12 xl:px-20 text-center">
            <p class="text-xs text-slate-400">&copy; {{ date('Y') }} Pinjamin - Sistem Peminjaman Alat Lab. Politeknik Negeri Semarang.</p>
        </div>
    </footer>

    <x-page-loader />
    @stack('scripts')
</body>
</html>
