@extends('layouts.admin')

@section('title', 'Denda')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Manajemen Denda</h2>
            <p class="text-sm text-slate-500 mt-1">Kelola denda keterlambatan dan kerusakan barang</p>
        </div>
        <div class="px-4 py-2.5 bg-red-50 rounded-2xl border border-red-100">
            <span class="text-xs text-red-400 font-bold uppercase tracking-wider">Total Belum Dibayar</span>
            <p class="text-lg font-black text-red-700">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex gap-2">
        @php
            $tabs = [
                'belum_dibayar' => ['label' => 'Belum Dibayar', 'color' => 'red'],
                'menunggu_verifikasi' => ['label' => 'Menunggu Verifikasi', 'color' => 'blue'],
                'lunas' => ['label' => 'Lunas', 'color' => 'emerald'],
            ];
        @endphp
        @foreach($tabs as $key => $tab)
            <a href="{{ route('admin.fines.index', ['status' => $key]) }}"
                class="px-4 py-2 rounded-xl text-sm font-bold border transition {{ $status === $key ? "bg-{$tab['color']}-50 text-{$tab['color']}-700 border-{$tab['color']}-200" : 'bg-white text-slate-500 border-slate-100 hover:bg-slate-50' }}">
                {{ $tab['label'] }}
                @if(($counts[$key] ?? 0) > 0)
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-{{ $tab['color'] }}-100 text-{{ $tab['color'] }}-700">{{ $counts[$key] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    <!-- Fines Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="py-4 px-6">ID Pinjam</th>
                        <th class="py-4 px-6">Mahasiswa</th>
                        <th class="py-4 px-6">Jenis</th>
                        <th class="py-4 px-6">Jumlah</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6">Bukti</th>
                        <th class="py-4 px-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm text-slate-600">
                    @forelse($fines as $fine)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-4 px-6 font-bold text-slate-800">L{{ str_pad($fine->loan_id, 3, '0', STR_PAD_LEFT) }}</td>
                            <td class="py-4 px-6">
                                <div class="font-semibold text-slate-800">{{ $fine->loan->user->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $fine->loan->user->nim }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $fine->type === 'keterlambatan' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                    {{ $fine->type === 'keterlambatan' ? 'Keterlambatan' : 'Kerusakan' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-800">Rp {{ number_format($fine->amount, 0, ',', '.') }}</td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $fine->status === 'lunas' ? 'bg-emerald-50 text-emerald-700' : ($fine->status === 'menunggu_verifikasi' ? 'bg-blue-50 text-blue-700' : 'bg-red-50 text-red-700') }}">
                                    {{ ucfirst(str_replace('_', ' ', $fine->status)) }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                @if($fine->payment_proof_photo)
                                    <a href="{{ asset('storage/' . $fine->payment_proof_photo) }}" target="_blank" class="text-teal-600 font-bold text-xs hover:underline">Lihat</a>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if($fine->status === 'menunggu_verifikasi')
                                    <div class="flex gap-2">
                                        <form action="{{ route('admin.fines.verify', $fine) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-bold hover:bg-emerald-100 border border-emerald-100 transition">✓</button>
                                        </form>
                                        <form action="{{ route('admin.fines.verify', $fine) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="px-2.5 py-1 bg-red-50 text-red-600 rounded-lg text-xs font-bold hover:bg-red-100 border border-red-100 transition">✕</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 px-6 text-center text-slate-400">Tidak ada denda dengan status ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $fines->links() }}</div>
</div>
@endsection
