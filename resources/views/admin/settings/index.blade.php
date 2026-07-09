@extends('layouts.admin')

@section('title', 'Pengaturan')

@section('content')
<div class="w-full space-y-6">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Pengaturan Sistem</h2>
        <p class="text-sm text-slate-500 mt-1">Konfigurasi global untuk sistem peminjaman</p>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-5">
        @csrf @method('PUT')

        {{-- ========== Durasi Peminjaman ========== --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-teal-100 rounded-lg text-teal-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-700">Durasi & Batas Peminjaman</h3>
                </div>
            </div>
            <div class="p-6 space-y-5">
                {{-- Durasi Maks --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Durasi Maksimal</label>
                        <div class="flex items-stretch rounded-xl border border-slate-200 overflow-hidden focus-within:ring-2 focus-within:ring-teal-500/30 focus-within:border-teal-400 transition @error('max_loan_duration') border-red-400 @enderror">
                            <input type="number" name="max_loan_duration"
                                value="{{ $settings['max_loan_duration']->value ?? 8 }}"
                                min="1" required
                                class="w-full px-4 py-2.5 text-sm font-semibold text-slate-800 bg-white focus:outline-none">
                            <select name="max_loan_duration_type"
                                class="px-3 py-2.5 text-sm font-semibold text-slate-600 bg-slate-50 border-l border-slate-200 focus:outline-none cursor-pointer">
                                <option value="hours" {{ ($settings['max_loan_duration_type']->value ?? 'hours') === 'hours' ? 'selected' : '' }}>Jam</option>
                                <option value="days" {{ ($settings['max_loan_duration_type']->value ?? 'hours') === 'days' ? 'selected' : '' }}>Hari</option>
                            </select>
                        </div>
                        <p class="text-xs text-slate-400 mt-1.5">Batas waktu maksimal per pengajuan</p>
                        @error('max_loan_duration') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Batas Barang --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Maks. Barang Dipinjam</label>
                        <div class="flex items-stretch rounded-xl border border-slate-200 overflow-hidden focus-within:ring-2 focus-within:ring-teal-500/30 focus-within:border-teal-400 transition @error('max_items_borrowed') border-red-400 @enderror">
                            <input type="number" name="max_items_borrowed"
                                value="{{ $settings['max_items_borrowed']->value ?? 3 }}"
                                min="1" max="10" required
                                class="w-full px-4 py-2.5 text-sm font-semibold text-slate-800 bg-white focus:outline-none">
                            <span class="px-4 py-2.5 text-sm font-semibold text-slate-500 bg-slate-50 border-l border-slate-200 flex items-center">barang</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1.5">Jumlah unit yang bisa dipinjam sekaligus</p>
                        @error('max_items_borrowed') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- ========== Denda ========== --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-red-100 rounded-lg text-red-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-700">Pengaturan Denda Keterlambatan</h3>
                </div>
            </div>
            <div class="p-6">
                <div class="max-w-md">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Tarif Denda</label>
                    <div class="flex items-stretch rounded-xl border border-slate-200 overflow-hidden focus-within:ring-2 focus-within:ring-teal-500/30 focus-within:border-teal-400 transition @error('fine_amount') border-red-400 @enderror">
                        <span class="px-4 py-2.5 text-sm font-semibold text-slate-500 bg-slate-50 border-r border-slate-200 flex items-center">Rp</span>
                        <input type="number" name="fine_amount"
                            value="{{ $settings['fine_amount']->value ?? 5000 }}"
                            min="0" max="1000000" required
                            class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-800 bg-white focus:outline-none">
                        <span class="px-3 py-2.5 text-sm font-bold text-slate-400 bg-slate-50 border-l border-slate-200 flex items-center">/</span>
                        <select name="fine_type"
                            class="px-4 py-2.5 text-sm font-semibold text-slate-600 bg-slate-50 border-l border-slate-200 focus:outline-none cursor-pointer">
                            <option value="per_hour" {{ ($settings['fine_type']->value ?? 'per_hour') === 'per_hour' ? 'selected' : '' }}>Jam</option>
                            <option value="per_day" {{ ($settings['fine_type']->value ?? 'per_hour') === 'per_day' ? 'selected' : '' }}>Hari</option>
                        </select>
                    </div>
                    <p class="text-xs text-slate-400 mt-1.5">Nominal denda yang dikenakan per satuan waktu keterlambatan</p>
                    @error('fine_amount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    @error('fine_type') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Preview card --}}
                <div class="mt-5 p-4 bg-amber-50 border border-amber-100 rounded-xl">
                    <p class="text-xs font-semibold text-amber-700">
                        💡 Contoh: Jika diisi <strong>Rp {{ number_format($settings['fine_amount']->value ?? 5000, 0, ',', '.') }} / {{ ($settings['fine_type']->value ?? 'per_hour') === 'per_day' ? 'Hari' : 'Jam' }}</strong>, 
                        maka mahasiswa yang terlambat 3 {{ ($settings['fine_type']->value ?? 'per_hour') === 'per_day' ? 'hari' : 'jam' }} akan dikenakan denda total 
                        <strong>Rp {{ number_format(($settings['fine_amount']->value ?? 5000) * 3, 0, ',', '.') }}</strong>.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-8 py-3 bg-teal-600 text-white rounded-xl font-bold text-sm hover:bg-teal-700 shadow-md shadow-teal-600/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection
