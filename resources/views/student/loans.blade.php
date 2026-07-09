@extends('layouts.student')

@section('title', 'Peminjaman Saya')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Riwayat Peminjaman</h2>
        <p class="text-sm text-slate-500 mt-1">Pantau status peminjaman Anda</p>
    </div>

    <div class="space-y-4">
        @forelse($loans as $loan)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800">Peminjaman L{{ str_pad($loan->id, 3, '0', STR_PAD_LEFT) }}</h3>
                        <p class="text-xs text-slate-400">{{ $loan->created_at->format('d M Y H:i') }} · {{ $loan->loan_duration_hours }} jam</p>
                    </div>
                    @php
                        $statusMap = [
                            'menunggu_persetujuan' => ['Menunggu Persetujuan', 'amber'],
                            'aktif' => ['Aktif', 'emerald'],
                            'menunggu_verifikasi_kembali' => ['Menunggu Verifikasi', 'blue'],
                            'terlambat' => ['Terlambat', 'red'],
                            'selesai' => ['Selesai', 'slate'],
                            'ditolak' => ['Ditolak', 'red'],
                        ];
                        [$label, $color] = $statusMap[$loan->status] ?? ['?', 'slate'];
                    @endphp
                    <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-{{ $color }}-50 text-{{ $color }}-700 border border-{{ $color }}-200">
                        {{ $label }}
                    </span>
                </div>

                <!-- Items -->
                <div class="space-y-2">
                    @foreach($loan->loanItems as $li)
                        <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="h-2 w-2 rounded-full bg-teal-500 shrink-0"></span>
                            <span class="text-sm font-semibold text-slate-700 flex-1">{{ $li->unit->item->name }}</span>
                            <span class="text-xs text-slate-400 bg-white px-2 py-0.5 rounded border border-slate-100">{{ $li->unit->serial_number }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-2">
                    <a href="{{ route('student.loans.show', $loan) }}" class="px-4 py-2 bg-slate-50 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-100 border border-slate-100 transition">
                        Lihat Detail
                    </a>
                    @if(in_array($loan->status, ['aktif', 'terlambat']))
                        <a href="{{ route('student.loans.show', $loan) }}#return" class="px-4 py-2 bg-teal-600 text-white rounded-xl text-xs font-bold hover:bg-teal-700 shadow-sm transition">
                            Ajukan Pengembalian
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-16 bg-white rounded-2xl border border-slate-100 shadow-sm">
                <p class="text-slate-400 font-semibold">Belum ada riwayat peminjaman.</p>
                <a href="{{ route('student.catalog') }}" class="text-teal-600 text-sm font-bold hover:underline mt-2 inline-block">Jelajahi katalog →</a>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $loans->links() }}</div>
</div>
@endsection
