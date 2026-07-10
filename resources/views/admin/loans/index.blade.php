@extends('layouts.admin')

@section('title', 'Peminjaman')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Manajemen Peminjaman</h2>
            <p class="text-sm text-slate-500 mt-1">Kelola seluruh permintaan dan status peminjaman</p>
        </div>
        <a href="{{ route('admin.loans.report', ['status' => $status]) }}" target="_blank"
            class="px-4 py-2.5 bg-teal-600 text-white rounded-xl text-sm font-bold hover:bg-teal-700 transition shadow-md shadow-teal-600/20">
            Cetak Laporan (PDF)
        </a>
    </div>

    <!-- Status Tabs -->
    <div class="flex flex-wrap gap-2">
        @php
            $tabs = [
                'menunggu_persetujuan' => ['label' => 'Menunggu', 'color' => 'amber'],
                'aktif' => ['label' => 'Aktif', 'color' => 'emerald'],
                'menunggu_verifikasi_kembali' => ['label' => 'Verifikasi Kembali', 'color' => 'blue'],
                'terlambat' => ['label' => 'Terlambat', 'color' => 'red'],
                'selesai' => ['label' => 'Selesai', 'color' => 'slate'],
                'ditolak' => ['label' => 'Ditolak', 'color' => 'slate'],
            ];
        @endphp
        @foreach($tabs as $key => $tab)
            <a href="{{ route('admin.loans.index', ['status' => $key]) }}"
                class="px-4 py-2 rounded-xl text-sm font-bold border transition {{ $status === $key ? "bg-{$tab['color']}-50 text-{$tab['color']}-700 border-{$tab['color']}-200" : 'bg-white text-slate-500 border-slate-100 hover:bg-slate-50' }}">
                {{ $tab['label'] }}
                @if(($counts[$key] ?? 0) > 0)
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-{{ $tab['color'] }}-100 text-{{ $tab['color'] }}-700">{{ $counts[$key] }}</span>
                @endif
            </a>
        @endforeach
    </div>

    <!-- Loans Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="py-4 px-6">ID</th>
                        <th class="py-4 px-6">Mahasiswa</th>
                        <th class="py-4 px-6">Barang</th>
                        <th class="py-4 px-6">Durasi</th>
                        <th class="py-4 px-6">Tanggal</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm text-slate-600">
                    @forelse($loans as $loan)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-4 px-6 font-bold text-slate-800">L{{ str_pad($loan->id, 3, '0', STR_PAD_LEFT) }}</td>
                            <td class="py-4 px-6">
                                <div class="font-semibold text-slate-800">{{ $loan->user->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $loan->user->nim }}</div>
                            </td>
                            <td class="py-4 px-6">
                                @foreach($loan->loanItems->take(2) as $li)
                                    <div class="text-xs">{{ $li->unit->item->name }} <span class="text-slate-400">({{ $li->unit->serial_number }})</span></div>
                                @endforeach
                                @if($loan->loanItems->count() > 2)
                                    <span class="text-[10px] text-slate-400">+{{ $loan->loanItems->count() - 2 }} lainnya</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-xs font-semibold">{{ $loan->duration_label }}</td>
                            <td class="py-4 px-6 text-xs text-slate-400">{{ $loan->created_at->format('d M Y H:i') }}</td>
                            <td class="py-4 px-6">
                                @php
                                    $statusMap = [
                                        'menunggu_persetujuan' => ['Menunggu', 'amber'],
                                        'aktif' => ['Aktif', 'emerald'],
                                        'menunggu_verifikasi_kembali' => ['Return', 'blue'],
                                        'terlambat' => ['Terlambat', 'red'],
                                        'selesai' => ['Selesai', 'slate'],
                                        'ditolak' => ['Ditolak', 'red'],
                                    ];
                                    [$label, $color] = $statusMap[$loan->status] ?? ['?', 'slate'];
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-{{ $color }}-50 text-{{ $color }}-700 border border-{{ $color }}-200">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <a href="{{ route('admin.loans.show', $loan) }}" class="px-3 py-1.5 bg-slate-50 text-slate-700 rounded-lg text-xs font-bold hover:bg-slate-100 border border-slate-100 transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 px-6 text-center text-slate-400">Tidak ada peminjaman dengan status ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $loans->links() }}</div>
</div>
@endsection
