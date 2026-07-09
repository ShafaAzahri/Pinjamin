@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="space-y-8" x-data="{ openKtmModal: false, activeKtmUrl: '', activeKtmName: '' }">
    
    <!-- Title Page -->
    <div>
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Dashboard</h2>
        <p class="text-sm text-slate-500 mt-1">Sistem Peminjaman Lab - Kelola aktivitas dan verifikasi hari ini</p>
    </div>

    <!-- Cards Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Stats Card 1 -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-md shadow-slate-100/40 flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Peminjaman Hari Ini</span>
                <p class="text-3xl font-black text-slate-800">{{ $totalLoansToday }}</p>
                <span class="text-[10px] text-emerald-600 font-bold flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0l-6.75 6.75M12 4.5l6.75 6.75" />
                    </svg>
                    Aktif dan tercatat
                </span>
            </div>
            <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
        </div>

        <!-- Stats Card 2 -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-md shadow-slate-100/40 flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Barang Dipinjam</span>
                <p class="text-3xl font-black text-slate-800">{{ $itemsBorrowed }}</p>
                <span class="text-[10px] text-slate-500 font-medium">Sedang aktif digunakan</span>
            </div>
            <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
        </div>

        <!-- Stats Card 3 -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-md shadow-slate-100/40 flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Denda Belum Bayar</span>
                <p class="text-3xl font-black text-slate-800">Rp {{ number_format($pendingFines, 0, ',', '.') }}</p>
                <span class="text-[10px] text-red-500 font-bold">Menunggu pembayaran</span>
            </div>
            <div class="h-12 w-12 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Main Content Section: Unverified KTM and Active Loans -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        <!-- Left 2 Cols: Active Loans -->
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md shadow-slate-100/40 overflow-hidden">
                <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-800">Peminjaman Aktif</h3>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-50 text-slate-600 border border-slate-100">
                        {{ $activeLoans->count() }} Peminjaman
                    </span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-100">
                                <th class="py-4 px-6">ID Pinjam</th>
                                <th class="py-4 px-6">Mahasiswa</th>
                                <th class="py-4 px-6">Daftar Alat</th>
                                <th class="py-4 px-6">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-sm text-slate-600">
                            @forelse($activeLoans as $loan)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-4 px-6 font-bold text-slate-800">L{{ str_pad($loan->id, 3, '0', STR_PAD_LEFT) }}</td>
                                    <td class="py-4 px-6">
                                        <div class="font-semibold text-slate-800">{{ $loan->user->name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $loan->user->nim }}</div>
                                    </td>
                                    <td class="py-4 px-6 space-y-1">
                                        @foreach($loan->loanItems as $lItem)
                                            <div class="flex items-center space-x-1.5">
                                                <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                                                <span class="font-medium text-slate-700">{{ $lItem->unit->item->name }}</span>
                                                <span class="text-[10px] bg-slate-100 px-1.5 py-0.5 rounded text-slate-500">
                                                    {{ $lItem->unit->serial_number }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($loan->status === 'terlambat')
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                                Terlambat
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Dipinjam
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 px-6 text-center text-slate-400">
                                        Tidak ada peminjaman aktif saat ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Col: Unverified KTM Users -->
        <div class="space-y-6">
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md shadow-slate-100/40 overflow-hidden">
                <div class="p-6 border-b border-slate-50">
                    <h3 class="text-lg font-bold text-slate-800">Verifikasi KTM Mahasiswa</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Tinjau pendaftaran mahasiswa baru</p>
                </div>

                <div class="p-6 divide-y divide-slate-50 space-y-4 max-h-[480px] overflow-y-auto">
                    @forelse($pendingUsers as $pUser)
                        <div class="pt-4 first:pt-0 flex flex-col space-y-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-slate-800 text-sm leading-tight">{{ $pUser->name }}</h4>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $pUser->nim }} - {{ $pUser->prodi }} ({{ $pUser->email }})</p>
                                </div>
                                <button type="button" 
                                    @click="activeKtmUrl = '{{ asset('storage/' . $pUser->ktm_photo) }}'; activeKtmName = '{{ $pUser->name }}'; openKtmModal = true"
                                    class="px-2.5 py-1.5 bg-slate-50 hover:bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold border border-slate-100 transition">
                                    Lihat KTM
                                </button>
                            </div>
                            
                            <div class="flex space-x-2">
                                <form action="{{ route('admin.users.verify', $pUser->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" 
                                        class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/10 transition">
                                        Setujui
                                    </button>
                                </form>
                                <form action="{{ route('admin.users.verify', $pUser->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" 
                                        class="w-full py-2 bg-slate-100 hover:bg-red-50 hover:text-red-700 hover:border-red-200 active:scale-[0.98] text-slate-500 text-xs font-bold rounded-xl border border-slate-100 transition">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-sm text-slate-400 py-6">
                            Tidak ada mahasiswa menunggu verifikasi.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- Modal KTM Photo View (Alpine.js handled) -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        x-show="openKtmModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak>
        <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl p-6 overflow-hidden transform transition-all"
            @click.away="openKtmModal = false">
            
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-slate-800">KTM: <span x-text="activeKtmName"></span></h3>
                <button type="button" @click="openKtmModal = false" class="p-1 rounded-lg text-slate-400 hover:bg-slate-50 hover:text-slate-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="rounded-2xl border border-slate-100 bg-slate-50 overflow-hidden flex items-center justify-center min-h-[250px]">
                <img :src="activeKtmUrl" alt="KTM Image" class="max-w-full max-h-[400px] object-contain">
            </div>
        </div>
    </div>

</div>
@endsection
