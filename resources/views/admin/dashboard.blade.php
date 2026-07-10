@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="space-y-8">
    
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

    <!-- Main Content Section: Active Loans -->
    <div class="space-y-6">
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
</div>

</div>
@endsection
