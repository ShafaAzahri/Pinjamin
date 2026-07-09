@extends('layouts.admin')

@section('title', 'Pengaturan')

@section('content')
<div class="max-w-2xl space-y-6">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Pengaturan Sistem</h2>
        <p class="text-sm text-slate-500 mt-1">Konfigurasi global untuk sistem peminjaman</p>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
        @csrf @method('PUT')

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
            <!-- Max Loan Duration -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Durasi Peminjaman Maksimal</label>
                <div class="flex items-center gap-3">
                    <input type="number" name="max_loan_duration" value="{{ $settings['max_loan_duration']->value ?? 8 }}" min="1" max="72" required
                        class="w-32 px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition @error('max_loan_duration') border-red-400 @enderror">
                    <span class="text-sm text-slate-500 font-medium">jam</span>
                </div>
                <p class="text-xs text-slate-400 mt-1">{{ $settings['max_loan_duration']->description ?? '' }}</p>
                @error('max_loan_duration') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Fine per Hour -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tarif Denda per Jam</label>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-slate-500 font-medium">Rp</span>
                    <input type="number" name="fine_per_hour" value="{{ $settings['fine_per_hour']->value ?? 5000 }}" min="0" max="100000" required
                        class="w-40 px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition @error('fine_per_hour') border-red-400 @enderror">
                </div>
                <p class="text-xs text-slate-400 mt-1">{{ $settings['fine_per_hour']->description ?? '' }}</p>
                @error('fine_per_hour') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Max Items -->
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Batas Barang per Peminjaman</label>
                <div class="flex items-center gap-3">
                    <input type="number" name="max_items_borrowed" value="{{ $settings['max_items_borrowed']->value ?? 3 }}" min="1" max="10" required
                        class="w-32 px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition @error('max_items_borrowed') border-red-400 @enderror">
                    <span class="text-sm text-slate-500 font-medium">barang</span>
                </div>
                <p class="text-xs text-slate-400 mt-1">{{ $settings['max_items_borrowed']->description ?? '' }}</p>
                @error('max_items_borrowed') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <button type="submit" class="px-6 py-3 bg-teal-600 text-white rounded-xl font-bold text-sm hover:bg-teal-700 shadow-md shadow-teal-600/20 transition">
            Simpan Pengaturan
        </button>
    </form>
</div>
@endsection
