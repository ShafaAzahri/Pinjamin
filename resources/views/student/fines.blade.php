@extends('layouts.student')

@section('title', 'Denda Saya')

@section('content')
<div class="w-full space-y-6">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Denda Saya</h2>
        <p class="text-sm text-slate-500 mt-1">Kelola dan bayar denda peminjaman Anda</p>
    </div>

    <div class="space-y-4">
        @forelse($fines as $fine)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4" x-data="{ showUpload: false }">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800">
                            Denda - L{{ str_pad($fine->loan_id, 3, '0', STR_PAD_LEFT) }}
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $fine->type === 'keterlambatan' ? 'Keterlambatan Pengembalian' : 'Kerusakan Barang' }}
                            · {{ $fine->created_at->format('d M Y') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-black text-slate-800">Rp {{ number_format($fine->amount, 0, ',', '.') }}</p>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $fine->status === 'lunas' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($fine->status === 'menunggu_verifikasi' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-red-50 text-red-700 border border-red-200') }}">
                            {{ ucfirst(str_replace('_', ' ', $fine->status)) }}
                        </span>
                    </div>
                </div>

                @if($fine->status === 'belum_dibayar')
                    <div class="pt-3 border-t border-slate-50">
                        <button @click="showUpload = !showUpload" class="px-4 py-2 bg-teal-600 text-white rounded-xl text-xs font-bold hover:bg-teal-700 shadow-sm transition">
                            Upload Bukti Pembayaran
                        </button>

                        <div x-show="showUpload" x-transition class="mt-4">
                            <form action="{{ route('student.fines.pay', $fine) }}" method="POST" enctype="multipart/form-data" class="flex gap-3 items-end">
                                @csrf
                                <div class="flex-1">
                                    <label class="block text-xs font-semibold text-slate-500 mb-1">Bukti Transfer/Pembayaran</label>
                                    <input type="file" name="payment_proof" accept="image/*" required
                                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-teal-50 file:text-teal-700">
                                </div>
                                <button type="submit" class="px-4 py-2.5 bg-teal-600 text-white rounded-lg text-sm font-bold hover:bg-teal-700 transition">
                                    Kirim
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                @if($fine->status === 'menunggu_verifikasi')
                    <div class="pt-3 border-t border-slate-50">
                        <div class="p-3 bg-blue-50 rounded-xl border border-blue-100">
                            <p class="text-xs text-blue-700 font-semibold">
                                ⏳ Bukti pembayaran Anda sedang ditinjau oleh Admin. Mohon tunggu verifikasi.
                            </p>
                        </div>
                    </div>
                @endif

                @if($fine->status === 'lunas')
                    <div class="pt-3 border-t border-slate-50">
                        <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                            <p class="text-xs text-emerald-700 font-semibold">
                                ✓ Pembayaran telah diverifikasi. Terima kasih!
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-16 bg-white rounded-2xl border border-slate-100 shadow-sm">
                <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-slate-400 font-semibold">Tidak ada denda. Bagus!</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $fines->links() }}</div>
</div>
@endsection
