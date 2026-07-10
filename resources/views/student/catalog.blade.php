@extends('layouts.student')

@section('title', 'Katalog Alat Lab')

@section('content')
<div class="space-y-6">
    <!-- Hero Banner -->
    <div class="relative bg-gradient-to-br from-slate-900 to-teal-900 rounded-3xl overflow-hidden shadow-xl mb-8">
        <!-- Decorative elements -->
        <div class="absolute inset-0 opacity-20">
            <svg class="absolute left-0 bottom-0 text-white w-64 h-64 -ml-16 -mb-16" fill="currentColor" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="50" />
            </svg>
            <svg class="absolute right-0 top-0 text-teal-400 w-96 h-96 -mr-32 -mt-32" fill="currentColor" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="50" />
            </svg>
        </div>

        <div class="relative z-10 px-6 py-12 sm:px-12 sm:py-16 md:py-20 flex flex-col items-center text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight mb-4">
                Selamat Datang, {{ explode(' ', Auth::user()->name)[0] }}!
            </h1>
            <p class="text-teal-50 text-sm sm:text-base md:text-lg max-w-2xl mb-10 opacity-90">
                Temukan dan pinjam berbagai peralatan laboratorium berkualitas untuk mendukung kegiatan praktikum dan penelitian Anda.
            </p>

            <!-- Search & Filter in Banner -->
            <div class="w-full max-w-4xl bg-white/10 backdrop-blur-md p-2 sm:p-3 rounded-2xl border border-white/20 shadow-lg">
                <form method="GET" class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-5 w-5 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama alat lab..."
                            class="w-full pl-11 pr-4 py-3 sm:py-4 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-teal-400 focus:bg-white/20 transition text-sm sm:text-base">
                    </div>
                    <select name="category" class="w-full sm:w-48 px-4 py-3 sm:py-4 bg-white/10 border border-white/20 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-teal-400 focus:bg-white/20 transition text-sm sm:text-base [&>option]:text-slate-800">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="w-full sm:w-auto px-8 py-3 sm:py-4 bg-teal-500 text-white rounded-xl text-sm sm:text-base font-bold hover:bg-teal-400 focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition shadow-md">
                        Cari Alat
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if(!auth()->user()->ktm_photo)
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl flex items-start shadow-sm">
            <svg class="w-5 h-5 mr-3 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <div class="text-sm">
                <span class="font-bold">Perhatian:</span> Anda belum mengunggah foto KTM. Silakan unggah KTM di menu <a href="{{ route('student.profile') }}" class="underline font-bold hover:text-amber-900">Profil Saya</a> terlebih dahulu agar dapat mengajukan peminjaman alat.
            </div>
        </div>
    @elseif(auth()->user()->status === 'menunggu_verifikasi')
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-2xl flex items-start shadow-sm">
            <svg class="w-5 h-5 mr-3 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-sm">
                <span class="font-bold">Informasi:</span> Foto KTM Anda telah diunggah dan sedang dalam proses verifikasi oleh Admin. Harap tunggu hingga akun Anda aktif untuk mengajukan peminjaman.
            </div>
        </div>
    @endif

    <!-- Items Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
        @forelse($items as $item)
            @php
                $availableUnits = $item->units->where('status', 'tersedia')->where('condition', 'baik');
                $totalUnits = $item->units->count();
            @endphp
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden group" x-data="{ showUnits: false }">
                <!-- Image -->
                <div class="h-36 bg-gradient-to-br from-slate-100 to-slate-50 flex items-center justify-center overflow-hidden">
                    @if($item->image)
                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    @else
                        <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    @endif
                </div>

                <div class="p-5 space-y-3">
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">{{ $item->name }}</h3>
                        <span class="text-xs text-slate-400">{{ $item->category->name ?? '-' }}</span>
                    </div>

                    <div class="flex gap-2 text-[10px] font-bold">
                        <span class="px-2 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100">{{ $availableUnits->count() }} tersedia</span>
                        <span class="px-2 py-1 rounded-lg bg-slate-50 text-slate-600 border border-slate-100">{{ $totalUnits }} total</span>
                    </div>

                    @if($availableUnits->count() > 0)
                        <button @click="showUnits = !showUnits" class="w-full py-2 bg-teal-50 text-teal-700 rounded-xl text-xs font-bold hover:bg-teal-100 border border-teal-100 transition">
                            <span x-text="showUnits ? 'Tutup' : 'Pilih Unit'"></span>
                        </button>

                        <!-- Unit Selection -->
                        <div x-show="showUnits" x-transition class="space-y-2 pt-2 border-t border-slate-50">
                            @foreach($availableUnits as $unit)
                                <div class="flex justify-between items-center p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                                    <span class="text-xs font-semibold text-slate-700">{{ $unit->serial_number }}</span>
                                    @if(in_array($unit->id, $cart))
                                        <form action="{{ route('student.cart.remove') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="item_unit_id" value="{{ $unit->id }}">
                                            <button type="submit" class="px-2.5 py-1 bg-red-50 text-red-600 rounded-lg text-[10px] font-bold hover:bg-red-100 border border-red-100 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('student.cart.add') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="item_unit_id" value="{{ $unit->id }}">
                                            <button type="submit" class="px-2.5 py-1 bg-teal-600 text-white rounded-lg text-[10px] font-bold hover:bg-teal-700 transition shadow-sm">
                                                + Keranjang
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-xs text-slate-400 py-2">Semua unit sedang dipinjam</p>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16">
                <p class="text-slate-400 font-semibold">Tidak ada alat ditemukan.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $items->links() }}</div>
</div>
@endsection
