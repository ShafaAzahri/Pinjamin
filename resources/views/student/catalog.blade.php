@extends('layouts.student')

@section('title', 'Katalog Alat Lab')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Katalog Alat Lab</h2>
        <p class="text-sm text-slate-500 mt-1">Pilih alat yang ingin Anda pinjam</p>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex flex-wrap gap-4 items-center">
        <form method="GET" class="flex-1 flex gap-3 items-center">
            <div class="flex-1 relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari alat..."
                    class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50/50 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition text-sm">
            </div>
            <select name="category" class="px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2.5 bg-slate-800 text-white rounded-xl text-sm font-bold hover:bg-slate-700 transition">
                Cari
            </button>
        </form>
    </div>

    <!-- Items Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
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
