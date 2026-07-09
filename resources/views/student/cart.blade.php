@extends('layouts.student')

@section('title', 'Keranjang')

@section('content')
<div class="w-full space-y-6">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Keranjang Peminjaman</h2>
        <p class="text-sm text-slate-500 mt-1">Review barang sebelum mengajukan peminjaman</p>
    </div>

    @if($units->count() > 0)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-50">
                <h3 class="font-bold text-slate-800">Barang Dipilih ({{ $units->count() }})</h3>
            </div>
            <div class="divide-y divide-slate-50">
                @foreach($units as $unit)
                    <div class="p-5 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                                @if($unit->item->image)
                                    <img src="{{ asset('storage/' . $unit->item->image) }}" class="w-full h-full object-cover rounded-xl">
                                @else
                                    <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm">{{ $unit->item->name }}</h4>
                                <p class="text-xs text-slate-400">{{ $unit->serial_number }} · {{ $unit->item->category->name ?? '-' }}</p>
                            </div>
                        </div>
                        <form action="{{ route('student.cart.remove') }}" method="POST">
                            @csrf
                            <input type="hidden" name="item_unit_id" value="{{ $unit->id }}">
                            <button type="submit" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Checkout Form -->
        <form action="{{ route('student.cart.checkout') }}" method="POST" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
            @csrf
            <h3 class="font-bold text-slate-800">Ajukan Peminjaman</h3>
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Durasi Peminjaman</label>
                <div class="flex items-center gap-3">
                    <input type="number" name="loan_duration_hours" value="{{ old('loan_duration_hours', $maxDuration) }}" min="1" max="{{ $maxDuration }}" required
                        class="w-32 px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 @error('loan_duration_hours') border-red-400 @enderror">
                    <span class="text-sm text-slate-500">jam (maks. {{ $maxDuration }} jam)</span>
                </div>
                @error('loan_duration_hours') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="p-4 bg-amber-50 rounded-xl border border-amber-100">
                <p class="text-xs text-amber-800 font-semibold">
                    ⚠️ Keterlambatan pengembalian akan dikenakan denda sesuai ketentuan yang berlaku. Pastikan mengembalikan tepat waktu.
                </p>
            </div>

            @if(!auth()->user()->ktm_photo)
                <div class="p-4 bg-amber-50 rounded-xl border border-amber-100 text-center">
                    <p class="text-sm text-amber-800 font-bold">
                        Anda harus mengunggah foto KTM di halaman <a href="{{ route('student.profile') }}" class="underline font-extrabold hover:text-amber-900">Profil Saya</a> terlebih dahulu sebelum mengajukan peminjaman.
                    </p>
                </div>
            @elseif(auth()->user()->status !== 'aktif')
                <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 text-center">
                    <p class="text-sm text-blue-800 font-bold">
                        Akun Anda sedang menunggu verifikasi KTM oleh Admin. Harap tunggu hingga akun Anda aktif.
                    </p>
                </div>
            @else
                <button type="submit" class="w-full py-3 bg-teal-600 text-white rounded-xl font-bold text-sm hover:bg-teal-700 shadow-md shadow-teal-600/20 transition">
                    Ajukan Peminjaman
                </button>
            @endif
        </form>
    @else
        <div class="text-center py-16 bg-white rounded-2xl border border-slate-100 shadow-sm">
            <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
            </svg>
            <p class="text-slate-400 font-semibold">Keranjang masih kosong.</p>
            <a href="{{ route('student.catalog') }}" class="text-teal-600 text-sm font-bold hover:underline mt-2 inline-block">Jelajahi katalog →</a>
        </div>
    @endif
</div>
@endsection
