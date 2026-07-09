<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Peminjaman Alat Lab - Pinjamin</title>
    <!-- Use Tailwind CSS v3 for styling -->
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
                color: black !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen p-6 sm:p-12 font-sans antialiased text-slate-800">

    <!-- Top Action Bar (hidden when printing) -->
    <div class="max-w-6xl mx-auto mb-8 bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-wrap gap-4 items-center justify-between no-print">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.loans.index') }}" class="text-sm font-semibold text-slate-500 hover:text-teal-600">
                ← Kembali
            </a>
            <span class="text-slate-200">|</span>
            <span class="text-sm text-slate-500 font-medium">Filter Laporan Aktif</span>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-sm font-bold shadow-md shadow-teal-600/10 transition">
                Cetak Laporan / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Printable Area Container -->
    <div class="max-w-6xl mx-auto bg-white rounded-3xl border border-slate-100 p-8 sm:p-12 shadow-sm space-y-8">
        
        <!-- Report Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-slate-100 pb-8 gap-6">
            <div>
                <div class="flex items-center mb-3">
                    <div class="h-8 w-8 rounded-lg bg-teal-500 flex items-center justify-center text-white font-extrabold text-lg mr-3">
                        P
                    </div>
                    <span class="text-xl font-black text-slate-800 tracking-tight">PINJAMIN</span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Laporan Aktivitas Peminjaman Alat</h1>
                <p class="text-sm text-slate-400 mt-1">Laboratorium Elektro & Mikro kontroler Politeknik Negeri Semarang</p>
            </div>
            <div class="text-left md:text-right text-sm space-y-1">
                <p class="font-bold text-slate-700">Tanggal Cetak: <span class="font-medium text-slate-500">{{ now()->format('d M Y H:i') }}</span></p>
                <p class="font-bold text-slate-700">Filter Status: 
                    <span class="font-medium text-slate-500">
                        {{ $status ? ucfirst(str_replace('_', ' ', $status)) : 'Semua Status' }}
                    </span>
                </p>
                @if($startDate || $endDate)
                    <p class="font-bold text-slate-700">Rentang Waktu: 
                        <span class="font-medium text-slate-500">
                            {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'Awal' }}
                            s/d
                            {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : 'Kini' }}
                        </span>
                    </p>
                @endif
            </div>
        </div>

        <!-- Loans Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[10px] text-slate-500 font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3.5 px-4 text-center">ID</th>
                        <th class="py-3.5 px-4">Mahasiswa</th>
                        <th class="py-3.5 px-4">Daftar Alat</th>
                        <th class="py-3.5 px-4">Tgl Pinjam</th>
                        <th class="py-3.5 px-4">Tgl Kembali</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Denda</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-600">
                    @forelse($loans as $loan)
                        <tr class="hover:bg-slate-50/30 transition">
                            <td class="py-4 px-4 text-center font-bold text-slate-800">
                                L{{ str_pad($loan->id, 3, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-800">{{ $loan->user->name }}</div>
                                <div class="text-[10px] text-slate-400 font-semibold">{{ $loan->user->nim }}</div>
                            </td>
                            <td class="py-4 px-4 space-y-1">
                                @foreach($loan->loanItems as $li)
                                    <div class="flex items-center gap-1.5">
                                        <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                                        <span class="font-medium text-slate-700">{{ $li->unit->item->name }}</span>
                                        <span class="text-[10px] text-slate-400">({{ $li->unit->serial_number }})</span>
                                    </div>
                                @endforeach
                            </td>
                            <td class="py-4 px-4 text-slate-500">
                                {{ $loan->approved_at ? \Carbon\Carbon::parse($loan->approved_at)->format('d M Y H:i') : '-' }}
                            </td>
                            <td class="py-4 px-4 text-slate-500">
                                {{ $loan->returned_at ? \Carbon\Carbon::parse($loan->returned_at)->format('d M Y H:i') : '-' }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                @php
                                    $statusMap = [
                                        'menunggu_persetujuan' => ['Menunggu', 'amber'],
                                        'aktif' => ['Aktif', 'emerald'],
                                        'menunggu_verifikasi_kembali' => ['Verifikasi', 'blue'],
                                        'terlambat' => ['Terlambat', 'red'],
                                        'selesai' => ['Selesai', 'slate'],
                                        'ditolak' => ['Ditolak', 'red'],
                                    ];
                                    [$label, $color] = $statusMap[$loan->status] ?? ['?', 'slate'];
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-{{ $color }}-50 text-{{ $color }}-700 border border-{{ $color }}-200">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right font-bold text-slate-800">
                                @php
                                    $fineAmount = $loan->fines->sum('amount');
                                @endphp
                                @if($fineAmount > 0)
                                    Rp {{ number_format($fineAmount, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 px-4 text-center text-slate-400">
                                Tidak ada data peminjaman yang cocok dengan kriteria laporan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Summary / Footer Statistics -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 pt-8 border-t border-slate-100">
            <div class="bg-slate-50/50 p-5 rounded-2xl border border-slate-100">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Total Peminjaman</span>
                <p class="text-2xl font-black text-slate-800 mt-1">{{ $loans->count() }}</p>
            </div>
            <div class="bg-slate-50/50 p-5 rounded-2xl border border-slate-100">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Peminjaman Selesai</span>
                <p class="text-2xl font-black text-slate-800 mt-1">
                    {{ $loans->where('status', 'selesai')->count() }}
                </p>
            </div>
            <div class="bg-slate-50/50 p-5 rounded-2xl border border-slate-100">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Terlambat / Aktif</span>
                <p class="text-2xl font-black text-slate-800 mt-1">
                    {{ $loans->whereIn('status', ['aktif', 'terlambat'])->count() }}
                </p>
            </div>
            <div class="bg-slate-50/50 p-5 rounded-2xl border border-slate-100">
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Total Denda Tercatat</span>
                <p class="text-2xl font-black text-slate-800 mt-1">
                    Rp {{ number_format($loans->flatMap->fines->sum('amount'), 0, ',', '.') }}
                </p>
            </div>
        </div>

        <!-- Signature Block (for formal report signing) -->
        <div class="flex justify-between items-end pt-12 text-sm">
            <div class="text-center w-48">
                <!-- left signature space -->
            </div>
            <div class="text-center w-64 space-y-16">
                <div>
                    <p class="text-slate-400 text-xs">Mengetahui,</p>
                    <p class="font-bold text-slate-700 mt-1">Kepala Laboratorium Elektro</p>
                </div>
                <div>
                    <div class="border-b border-slate-400 w-full mx-auto"></div>
                    <p class="font-bold text-slate-600 text-xs mt-1.5">NIP. 19780512 200501 1 002</p>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
