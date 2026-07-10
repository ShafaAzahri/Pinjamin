@extends('layouts.admin')

@section('title', 'Verifikasi Akun Mahasiswa')

@section('content')
<div class="w-full space-y-6" x-data="{ openKtmModal: false, activeKtmUrl: '', activeKtmName: '' }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Verifikasi Akun Mahasiswa</h2>
            <p class="text-sm text-slate-500 mt-1">Daftar pendaftaran akun mahasiswa baru yang memerlukan verifikasi identitas KTM</p>
        </div>
        <div class="bg-teal-50 text-teal-800 border border-teal-100 rounded-2xl px-4 py-2 text-xs font-bold self-start sm:self-auto flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-teal-500 animate-pulse"></span>
            {{ $pendingUsers->total() }} Mahasiswa Menunggu Verifikasi
        </div>
    </div>

    <!-- Main List Card -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="py-4 px-6">Tanggal Daftar</th>
                        <th class="py-4 px-6">Identitas Mahasiswa</th>
                        <th class="py-4 px-6">Dokumen Pendukung</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm text-slate-600">
                    @forelse($pendingUsers as $pUser)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-5 px-6 font-semibold text-slate-500">
                                {{ $pUser->created_at->translatedFormat('d M Y H:i') }}
                            </td>
                            <td class="py-5 px-6">
                                <div class="font-bold text-slate-800">{{ $pUser->name }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $pUser->nim }} • {{ $pUser->prodi }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $pUser->email }}</div>
                            </td>
                            <td class="py-5 px-6">
                                <button type="button" 
                                    @click="activeKtmUrl = '{{ asset('storage/' . $pUser->ktm_photo) }}'; activeKtmName = '{{ $pUser->name }}'; openKtmModal = true"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-50 hover:bg-teal-50 hover:text-teal-700 hover:border-teal-200 text-slate-600 rounded-xl text-xs font-semibold border border-slate-200/60 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Lihat Foto KTM
                                </button>
                            </td>
                            <td class="py-5 px-6">
                                <div class="flex items-center justify-end gap-2">
                                    <form action="{{ route('admin.users.verify', $pUser->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" 
                                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/10 transition">
                                            Setujui Akun
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.users.verify', $pUser->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" 
                                            class="px-4 py-2 bg-slate-100 hover:bg-red-50 hover:text-red-700 hover:border-red-200 active:scale-[0.98] text-slate-500 text-xs font-bold rounded-xl border border-slate-200/50 transition">
                                            Tolak & Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 px-6 text-center">
                                <div class="max-w-sm mx-auto space-y-2">
                                    <div class="h-12 w-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-bold text-slate-800">Tidak Ada Antrean Verifikasi</p>
                                    <p class="text-xs text-slate-400">Semua pendaftaran mahasiswa baru telah diproses.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pendingUsers->hasPages())
            <div class="p-6 border-t border-slate-50 bg-slate-50/30">
                {{ $pendingUsers->links() }}
            </div>
        @endif
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
