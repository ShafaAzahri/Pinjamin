<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Lab A') - Pinjamin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Alpine.js CDN for quick interactive elements like modals/dropdowns -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script type="module" src="https://cdn.skypack.dev/@hotwired/turbo"></script>
</head>
<body class="h-full flex font-sans antialiased bg-slate-50">
    
    <!-- Sidebar -->
    <aside class="w-64 min-w-[16rem] bg-slate-900 text-slate-300 flex flex-col justify-between shrink-0 shadow-xl shadow-slate-900/10">
        <div>
            <!-- Brand -->
            <div class="h-32 flex items-center justify-center px-6 border-b border-slate-800">
                <img src="{{ asset('images/pinjamin-logo.png') }}" alt="PINJAMIN Logo" class="h-24 w-auto bg-white rounded-xl p-2 shadow-sm">
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-1">
                <a href="/admin/dashboard" 
                    class="flex items-center px-4 py-3 rounded-xl transition duration-150 font-medium text-sm {{ request()->is('admin/dashboard') ? 'bg-teal-700/20 text-teal-400 border border-teal-500/20' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200 border border-transparent' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                    </svg>
                    Dashboard
                </a>

                <a href="/admin/users/verification" 
                    class="flex items-center px-4 py-3 rounded-xl transition duration-150 font-medium text-sm {{ request()->is('admin/users/verification*') ? 'bg-teal-700/20 text-teal-400 border border-teal-500/20' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200 border border-transparent' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    Verifikasi Akun
                    @php
                        $pendingVerificationCount = \App\Models\User::where('role', 'user')->where('status', 'menunggu_verifikasi')->count();
                    @endphp
                    @if($pendingVerificationCount > 0)
                        <span class="ml-auto bg-teal-500 text-slate-900 text-[10px] font-bold px-2 py-0.5 rounded-full">
                            {{ $pendingVerificationCount }}
                        </span>
                    @endif
                </a>

                <a href="/admin/inventory" 
                    class="flex items-center px-4 py-3 rounded-xl transition duration-150 font-medium text-sm {{ request()->is('admin/inventory*') ? 'bg-teal-700/20 text-teal-400 border border-teal-500/20' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200 border border-transparent' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    Inventaris Barang
                </a>

                <a href="/admin/loans" 
                    class="flex items-center px-4 py-3 rounded-xl transition duration-150 font-medium text-sm {{ request()->is('admin/loans*') ? 'bg-teal-700/20 text-teal-400 border border-teal-500/20' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200 border border-transparent' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    Peminjaman
                </a>

                <a href="/admin/fines" 
                    class="flex items-center px-4 py-3 rounded-xl transition duration-150 font-medium text-sm {{ request()->is('admin/fines*') ? 'bg-teal-700/20 text-teal-400 border border-teal-500/20' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200 border border-transparent' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Denda
                </a>

                <a href="/admin/settings" 
                    class="flex items-center px-4 py-3 rounded-xl transition duration-150 font-medium text-sm {{ request()->is('admin/settings*') ? 'bg-teal-700/20 text-teal-400 border border-teal-500/20' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200 border border-transparent' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Pengaturan
                </a>

                <a href="/admin/whatsapp" 
                    class="flex items-center px-4 py-3 rounded-xl transition duration-150 font-medium text-sm {{ request()->is('admin/whatsapp*') ? 'bg-teal-700/20 text-teal-400 border border-teal-500/20' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200 border border-transparent' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    Hubungkan ke Whatsapp
                </a>
            </nav>
        </div>

        <!-- Admin Profile / Logout -->
        <div class="p-4 border-t border-slate-800 flex items-center justify-between">
            <div class="flex items-center">
                <div class="h-9 w-9 rounded-full bg-slate-700 flex items-center justify-center text-white font-bold text-sm shadow-inner mr-3 uppercase">
                    {{ substr(Auth::user()->name, 0, 2) }}
                </div>
                <div class="overflow-hidden">
                    <p class="text-sm font-bold text-white truncate leading-none mb-1">{{ Auth::user()->name }}</p>
                    <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider">Admin</span>
                </div>
            </div>
            <form action="/logout" method="POST" class="ml-2">
                @csrf
                <button type="submit" title="Keluar" class="p-2 text-slate-500 hover:text-red-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Panel -->
    <div class="flex-1 min-w-0 flex flex-col overflow-hidden">
        
        <!-- Header -->
        <header class="h-16 bg-white/80 backdrop-blur-md border-b border-slate-100 px-8 flex items-center justify-between shrink-0">
            <!-- Search bar placeholder -->
            <div class="w-72 relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" placeholder="Cari..." 
                    class="w-full pl-10 pr-4 py-2 border border-slate-100 rounded-xl bg-slate-50/50 focus:outline-none focus:border-teal-500 transition duration-150 text-sm">
            </div>

            <!-- Utility controls -->
            <div class="flex items-center space-x-4">
                <span class="text-xs text-slate-400 font-medium">Tersinkronisasi</span>
                <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
            </div>
        </header>

        <!-- Main Workspace -->
        <main class="flex-1 overflow-y-auto p-8">
            <!-- Success/Alert Feedbacks -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-2xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center text-sm font-semibold">
                        <svg class="w-5 h-5 mr-3 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-100 text-red-800 rounded-2xl flex items-center shadow-sm">
                    <div class="flex items-center text-sm font-semibold">
                        <svg class="w-5 h-5 mr-3 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <x-page-loader />
    @stack('scripts')
</body>
</html>
