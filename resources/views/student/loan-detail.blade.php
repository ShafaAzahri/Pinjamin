@extends('layouts.student')

@section('title', 'Detail Peminjaman L' . str_pad($loan->id, 3, '0', STR_PAD_LEFT))

@section('content')
<div class="w-full space-y-6">
    <div>
        <a href="{{ route('student.loans') }}" class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 text-slate-500 rounded-xl text-xs font-bold hover:bg-slate-50 hover:text-teal-600 transition shadow-sm mb-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
            </svg>
            Kembali
        </a>
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-2">
            Peminjaman L{{ str_pad($loan->id, 3, '0', STR_PAD_LEFT) }}
        </h2>
    </div>

    <!-- Status Card -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
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
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Tanggal</span>
                <p class="font-bold text-slate-800 text-sm">{{ $loan->created_at->format('d M Y H:i') }}</p>
            </div>
        </div>
    </div>

    <!-- Estimasi Denda Berjalan -->
    @if($loan->status === 'terlambat' && $loan->approved_at)
        @php
            $finePerHour = (int) (\App\Models\Setting::where('key', 'fine_per_hour')->first()?->value ?? 5000);
            $deadline = \Carbon\Carbon::parse($loan->approved_at)->addHours($loan->loan_duration_hours);
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
                    <div>
                        <h3 class="font-bold text-red-800 text-lg">Peringatan: Denda Berjalan</h3>
                        <p class="text-red-700 text-sm mt-1 leading-relaxed">
                            Peminjaman Anda telah melewati batas waktu pengembalian pada <strong>{{ $deadline->format('d M Y H:i') }} WIB</strong>. 
                            Estimasi denda keterlambatan saat ini adalah <strong class="text-red-800 text-base">Rp {{ number_format($estimatedFine, 0, ',', '.') }}</strong>.
                        </p>
                        <p class="text-red-600 text-xs mt-2 italic font-semibold">
                            *Harap SEGERA mengembalikan barang ke Laboratorium agar denda tidak terus bertambah, lalu bayar tagihan resmi Anda melalui aplikasi.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- Items -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-50">
            <h3 class="font-bold text-slate-800">Barang yang Dipinjam</h3>
        </div>
        <div class="divide-y divide-slate-50">
            @foreach($loan->loanItems as $li)
                <div class="p-5 flex items-center justify-between">
                    <div>
                        <h4 class="font-semibold text-slate-800 text-sm">{{ $li->unit->item->name }}</h4>
                        <p class="text-xs text-slate-400">{{ $li->unit->serial_number }}</p>
                    </div>
                    @if($li->return_condition)
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $li->return_condition === 'baik' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                            {{ ucfirst($li->return_condition) }}
                        </span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Return Form -->
    @if(in_array($loan->status, ['aktif', 'terlambat']))
        <div id="return" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-slate-800">Ajukan Pengembalian</h3>
            <p class="text-sm text-slate-500">Unggah foto bukti pengembalian untuk setiap barang.</p>

            <form action="{{ route('student.loans.return', $loan) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @foreach($loan->loanItems as $li)
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 space-y-2">
                        <label class="block text-sm font-semibold text-slate-700">
                            {{ $li->unit->item->name }} ({{ $li->unit->serial_number }})
                        </label>
                        <input type="file" name="return_photos[{{ $li->id }}]" accept="image/*" required
                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-teal-50 file:text-teal-700">
                    </div>
                @endforeach

                <button type="submit" class="w-full py-3 bg-teal-600 text-white rounded-xl font-bold text-sm hover:bg-teal-700 shadow-md shadow-teal-600/20 transition">
                    Kirim Pengembalian
                </button>
            </form>
        </div>
    @endif

    <!-- Fines -->
    @if($loan->fines->count() > 0)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-3">
            <h3 class="font-bold text-slate-800">Denda</h3>
            @foreach($loan->fines as $fine)
                <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <div>
                        <span class="font-bold text-sm text-slate-800">Rp {{ number_format($fine->amount, 0, ',', '.') }}</span>
                        <span class="text-xs text-slate-400 ml-2">({{ $fine->type === 'keterlambatan' ? 'Keterlambatan' : 'Kerusakan' }})</span>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $fine->status === 'lunas' ? 'bg-emerald-50 text-emerald-700' : ($fine->status === 'menunggu_verifikasi' ? 'bg-blue-50 text-blue-700' : 'bg-red-50 text-red-700') }}">
                        {{ ucfirst(str_replace('_', ' ', $fine->status)) }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
