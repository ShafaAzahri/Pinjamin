@extends('layouts.student')

@section('title', 'Denda Saya')

@section('content')
<div class="w-full space-y-6">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Denda Saya</h2>
        <p class="text-sm text-slate-500 mt-1">Kelola dan bayar denda peminjaman Anda</p>
    </div>

    <div class="space-y-4">
        <!-- Tampilkan Estimasi Denda Berjalan jika ada peminjaman terlambat -->
        @if(isset($runningFines) && $runningFines->count() > 0)
            @php
                $finePerHour = (int) (\App\Models\Setting::where('key', 'fine_per_hour')->first()?->value ?? 5000);
            @endphp
            @foreach($runningFines as $rLoan)
                @php
                    $deadline = \Carbon\Carbon::parse($rLoan->approved_at)->addHours($rLoan->loan_duration_hours);
                    $overdueHours = max(0, (int) ceil(abs(now()->diffInMinutes($deadline)) / 60));
                    $estimatedFine = $overdueHours * $finePerHour;
                @endphp
                @if($estimatedFine > 0)
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-5 shadow-sm">
                        <div class="flex items-start gap-4">
                            <div class="p-3 bg-red-100 rounded-full text-red-600 shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-red-800 text-lg">Peringatan: Denda Berjalan (L{{ str_pad($rLoan->id, 3, '0', STR_PAD_LEFT) }})</h3>
                                <p class="text-red-700 text-sm mt-1 leading-relaxed">
                                    Anda telah terlambat mengembalikan barang selama <strong>{{ $overdueHours }} jam</strong>. Estimasi denda saat ini adalah <strong class="text-red-800 text-base">Rp {{ number_format($estimatedFine, 0, ',', '.') }}</strong>.
                                </p>
                                <p class="text-red-600 text-xs mt-2 italic font-semibold">
                                    *Harap SEGERA mengembalikan barang ke Laboratorium agar denda berhenti, lalu bayar tagihan resmi Anda melalui aplikasi.
                                </p>
                            </div>
                            <div class="shrink-0 mt-1">
                                <a href="{{ route('student.loans.show', $rLoan) }}" class="px-4 py-2 bg-red-600 text-white rounded-xl text-xs font-bold hover:bg-red-700 shadow-sm transition">
                                    Kembalikan Sekarang
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif

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
                    <div class="pt-3 border-t border-slate-50" x-data="{ loading: false }">
                        <button 
                            @click="
                                loading = true;
                                fetch('{{ route('student.fines.snap-token', $fine) }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(res => res.json())
                                .then(data => {
                                    loading = false;
                                    if (data.snap_token) {
                                        window.snap.pay(data.snap_token, {
                                            onSuccess: function(result){
                                                window.location.reload();
                                            },
                                            onPending: function(result){
                                                alert('Menunggu pembayaran Anda!');
                                            },
                                            onError: function(result){
                                                alert('Pembayaran gagal!');
                                            },
                                            onClose: function(){
                                                console.log('User closed popup');
                                            }
                                        });
                                    } else {
                                        alert(data.error || 'Terjadi kesalahan saat memproses pembayaran.');
                                    }
                                })
                                .catch(err => {
                                    loading = false;
                                    alert('Kesalahan jaringan.');
                                });
                            " 
                            :disabled="loading"
                            class="px-4 py-2 bg-teal-600 text-white rounded-xl text-xs font-bold hover:bg-teal-700 shadow-sm transition disabled:opacity-50">
                            <span x-show="!loading">Bayar via Midtrans</span>
                            <span x-show="loading">Memproses...</span>
                        </button>
                    </div>
                @endif

                @if($fine->status === 'menunggu_verifikasi')
                    <div class="pt-3 border-t border-slate-50">
                        <div class="p-3 bg-blue-50 rounded-xl border border-blue-100">
                            <p class="text-xs text-blue-700 font-semibold">
                                ⏳ Bukti pembayaran manual Anda sedang ditinjau oleh Admin.
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

@push('scripts')
    <script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
@endpush
@endsection
