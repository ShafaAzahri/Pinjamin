@extends('layouts.admin')

@section('title', 'Detail Peminjaman L' . str_pad($loan->id, 3, '0', STR_PAD_LEFT))

@section('content')
<div class="max-w-4xl space-y-6" x-data="{ showRejectModal: false }">
    <div>
        <a href="{{ route('admin.loans.index') }}" class="text-sm text-slate-500 hover:text-teal-600 font-semibold transition">← Kembali ke Daftar</a>
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-2">
            Peminjaman L{{ str_pad($loan->id, 3, '0', STR_PAD_LEFT) }}
        </h2>
    </div>

    <!-- Loan Info Card -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Mahasiswa</span>
                <p class="font-bold text-slate-800">{{ $loan->user->name }}</p>
                <p class="text-xs text-slate-400">{{ $loan->user->nim }}</p>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Status</span>
                @php
                    $statusMap = [
                        'menunggu_persetujuan' => ['Menunggu Persetujuan', 'amber'],
                        'aktif' => ['Aktif', 'emerald'],
                        'menunggu_verifikasi_kembali' => ['Menunggu Verifikasi Kembali', 'blue'],
                        'terlambat' => ['Terlambat', 'red'],
                        'selesai' => ['Selesai', 'slate'],
                        'ditolak' => ['Ditolak', 'red'],
                    ];
                    [$label, $color] = $statusMap[$loan->status] ?? ['?', 'slate'];
                @endphp
                <p class="mt-1"><span class="px-2.5 py-1 rounded-full text-xs font-bold bg-{{ $color }}-50 text-{{ $color }}-700 border border-{{ $color }}-200">{{ $label }}</span></p>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Durasi</span>
                <p class="font-bold text-slate-800">{{ $loan->loan_duration_hours }} jam</p>
            </div>
            <div>
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Tanggal Pengajuan</span>
                <p class="font-bold text-slate-800">{{ $loan->created_at->format('d M Y H:i') }}</p>
            </div>
        </div>

        @if($loan->approved_at)
            <div class="pt-3 border-t border-slate-50 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Disetujui Oleh</span>
                    <p class="font-semibold text-slate-700 text-sm">{{ $loan->approvedBy->name ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Waktu Disetujui</span>
                    <p class="font-semibold text-slate-700 text-sm">{{ \Carbon\Carbon::parse($loan->approved_at)->format('d M Y H:i') }}</p>
                </div>
                @if($loan->returned_at)
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Waktu Dikembalikan</span>
                        <p class="font-semibold text-slate-700 text-sm">{{ \Carbon\Carbon::parse($loan->returned_at)->format('d M Y H:i') }}</p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Loan Items -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-50">
            <h3 class="text-lg font-bold text-slate-800">Barang yang Dipinjam</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 text-[10px] text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3 px-6">Barang</th>
                        <th class="py-3 px-6">Serial</th>
                        <th class="py-3 px-6">Bukti Kembali</th>
                        <th class="py-3 px-6">Kondisi Kembali</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm">
                    @foreach($loan->loanItems as $li)
                        <tr>
                            <td class="py-4 px-6 font-semibold text-slate-800">{{ $li->unit->item->name }}</td>
                            <td class="py-4 px-6 text-slate-500">{{ $li->unit->serial_number }}</td>
                            <td class="py-4 px-6">
                                @if($li->return_proof_photo)
                                    <a href="{{ asset('storage/' . $li->return_proof_photo) }}" target="_blank" class="text-teal-600 font-bold text-xs hover:underline">Lihat Foto</a>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if($li->return_condition)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $li->return_condition === 'baik' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                        {{ ucfirst($li->return_condition) }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">Belum dikembalikan</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Action Buttons -->
    @if($loan->status === 'menunggu_persetujuan')
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Tindakan</h3>
            <div class="flex gap-3">
                <form action="{{ route('admin.loans.approve', $loan) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white rounded-xl font-bold text-sm hover:bg-emerald-700 shadow-md shadow-emerald-600/20 transition">
                        ✓ Setujui Peminjaman
                    </button>
                </form>
                <button @click="showRejectModal = true" class="px-6 py-2.5 bg-red-50 text-red-600 rounded-xl font-bold text-sm hover:bg-red-100 border border-red-100 transition">
                    ✕ Tolak Peminjaman
                </button>
            </div>
        </div>
    @endif

    @if($loan->status === 'menunggu_verifikasi_kembali')
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Verifikasi Pengembalian</h3>
            <form action="{{ route('admin.loans.verify-return', $loan) }}" method="POST" class="space-y-4">
                @csrf
                @foreach($loan->loanItems as $li)
                    <div class="flex items-center gap-4 p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="font-semibold text-sm text-slate-800 flex-1">{{ $li->unit->item->name }} ({{ $li->unit->serial_number }})</span>
                        <select name="unit_conditions[{{ $li->id }}]" class="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                            <option value="baik">Baik</option>
                            <option value="rusak">Rusak</option>
                        </select>
                    </div>
                @endforeach
                <button type="submit" class="px-6 py-2.5 bg-teal-600 text-white rounded-xl font-bold text-sm hover:bg-teal-700 shadow-md shadow-teal-600/20 transition">
                    Verifikasi Pengembalian
                </button>
            </form>
        </div>
    @endif

    <!-- Fines Section -->
    @if($loan->fines->count() > 0)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-50">
                <h3 class="text-lg font-bold text-slate-800">Denda Terkait</h3>
            </div>
            <div class="p-6 space-y-3">
                @foreach($loan->fines as $fine)
                    <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <div>
                            <span class="font-bold text-sm text-slate-800">Rp {{ number_format($fine->amount, 0, ',', '.') }}</span>
                            <span class="text-xs text-slate-400 ml-2">({{ $fine->type === 'keterlambatan' ? 'Keterlambatan' : 'Kerusakan Barang' }})</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $fine->status === 'lunas' ? 'bg-emerald-50 text-emerald-700' : ($fine->status === 'menunggu_verifikasi' ? 'bg-blue-50 text-blue-700' : 'bg-red-50 text-red-700') }}">
                            {{ ucfirst(str_replace('_', ' ', $fine->status)) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Reject Modal -->
    <div x-show="showRejectModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full" @click.away="showRejectModal = false">
            <h3 class="text-lg font-bold text-slate-800 mb-3">Tolak Peminjaman</h3>
            <form action="{{ route('admin.loans.reject', $loan) }}" method="POST" class="space-y-4">
                @csrf
                <textarea name="rejection_reason" rows="3" placeholder="Alasan penolakan (opsional)..." 
                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 resize-none"></textarea>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 py-2.5 bg-red-600 text-white rounded-xl font-bold text-sm hover:bg-red-700 transition">Tolak</button>
                    <button type="button" @click="showRejectModal = false" class="flex-1 py-2.5 bg-slate-100 text-slate-600 rounded-xl font-bold text-sm hover:bg-slate-200 transition">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
