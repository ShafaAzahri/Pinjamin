@extends('layouts.admin')

@section('title', 'Inventaris Barang')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Inventaris Barang</h2>
            <p class="text-sm text-slate-500 mt-1">Kelola seluruh alat dan unit laboratorium</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.categories.index') }}" 
                class="px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-bold hover:bg-slate-50 transition shadow-sm">
                Kategori
            </a>
            <a href="{{ route('admin.inventory.create') }}" 
                class="px-4 py-2.5 bg-teal-600 text-white rounded-xl text-sm font-bold hover:bg-teal-700 transition shadow-md shadow-teal-600/20">
                + Tambah Barang
            </a>
        </div>
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
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari barang..."
                    class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50/50 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition text-sm">
            </div>
            <select name="category" class="px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2.5 bg-slate-800 text-white rounded-xl text-sm font-bold hover:bg-slate-700 transition">
                Filter
            </button>
        </form>
    </div>

    <!-- Items Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($items as $item)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition overflow-hidden group">
                <!-- Image -->
                <div class="h-40 bg-gradient-to-br from-slate-100 to-slate-50 flex items-center justify-center overflow-hidden">
                    @if($item->image)
                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    @else
                        <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    @endif
                </div>
                <!-- Info -->
                <div class="p-5 space-y-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-bold text-slate-800 text-base leading-tight">{{ $item->name }}</h3>
                            <span class="text-xs text-slate-400 font-medium">{{ $item->category->name ?? '-' }}</span>
                        </div>
                    </div>
                    
                    <!-- Unit Stats -->
                    @php
                        $total = $item->units->count();
                        $available = $item->units->where('status', 'tersedia')->count();
                        $borrowed = $item->units->where('status', 'dipinjam')->count();
                        $maintenance = $item->units->where('status', 'maintenance')->count();
                    @endphp
                    <div class="flex gap-2 text-[10px] font-bold">
                        <span class="px-2 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100">{{ $available }} Tersedia</span>
                        <span class="px-2 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-100">{{ $borrowed }} Dipinjam</span>
                        @if($maintenance > 0)
                            <span class="px-2 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-100">{{ $maintenance }} Maintenance</span>
                        @endif
                    </div>

                    <div class="flex gap-2 pt-1">
                        <a href="{{ route('admin.inventory.show', $item) }}" 
                            class="flex-1 text-center px-3 py-2 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-bold border border-slate-100 transition">
                            Detail
                        </a>
                        <a href="{{ route('admin.inventory.edit', $item) }}" 
                            class="flex-1 text-center px-3 py-2 bg-teal-50 hover:bg-teal-100 text-teal-700 rounded-xl text-xs font-bold border border-teal-100 transition">
                            Edit
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16">
                <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <p class="text-slate-400 font-semibold">Belum ada barang terdaftar.</p>
                <a href="{{ route('admin.inventory.create') }}" class="text-teal-600 text-sm font-bold hover:underline mt-2 inline-block">Tambah barang pertama →</a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $items->links() }}
    </div>
</div>
@endsection
