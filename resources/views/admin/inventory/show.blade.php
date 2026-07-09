@extends('layouts.admin')

@section('title', $item->name)

@section('content')
<div class="space-y-6" x-data="{ showAddUnit: false, showDeleteModal: false }">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.inventory.index') }}" class="text-sm text-slate-500 hover:text-teal-600 font-semibold transition">← Kembali ke Inventaris</a>
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-2">{{ $item->name }}</h2>
            <p class="text-sm text-slate-400 mt-0.5">{{ $item->category->name ?? '-' }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.inventory.edit', $item) }}" 
                class="px-4 py-2.5 bg-teal-50 text-teal-700 rounded-xl text-sm font-bold hover:bg-teal-100 border border-teal-100 transition">
                Edit Barang
            </a>
            <button @click="showDeleteModal = true" class="px-4 py-2.5 bg-red-50 text-red-600 rounded-xl text-sm font-bold hover:bg-red-100 border border-red-100 transition">
                Hapus
            </button>
        </div>
    </div>

    <!-- Item Info -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <div class="flex gap-6">
            <div class="h-32 w-32 rounded-2xl bg-slate-100 flex items-center justify-center shrink-0 overflow-hidden">
                @if($item->image)
                    <img src="{{ asset('storage/' . $item->image) }}" class="w-full h-full object-cover">
                @else
                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                @endif
            </div>
            <div class="space-y-2">
                <p class="text-sm text-slate-600">{{ $item->description ?? 'Tidak ada deskripsi.' }}</p>
                @php
                    $total = $item->units->count();
                    $available = $item->units->where('status', 'tersedia')->count();
                    $borrowed = $item->units->where('status', 'dipinjam')->count();
                    $maintenance = $item->units->where('status', 'maintenance')->count();
                @endphp
                <div class="flex gap-3 text-xs font-bold">
                    <span class="px-3 py-1.5 rounded-lg bg-slate-50 text-slate-600 border border-slate-100">{{ $total }} Total Unit</span>
                    <span class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100">{{ $available }} Tersedia</span>
                    <span class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 border border-blue-100">{{ $borrowed }} Dipinjam</span>
                    <span class="px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-100">{{ $maintenance }} Maintenance</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Units Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800">Daftar Unit</h3>
            <button @click="showAddUnit = !showAddUnit" class="px-3 py-1.5 bg-teal-600 text-white rounded-xl text-xs font-bold hover:bg-teal-700 transition shadow-sm">
                + Tambah Unit
            </button>
        </div>

        <!-- Add Unit Form (toggle) -->
        <div x-show="showAddUnit" x-transition class="p-6 bg-teal-50/50 border-b border-teal-100">
            <form action="{{ route('admin.inventory.units.add', $item) }}" method="POST" class="flex gap-3 items-end">
                @csrf
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Nomor Seri</label>
                    <input type="text" name="serial_number" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                </div>
                <div class="w-36">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">Kondisi</label>
                    <select name="condition" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                        <option value="baik">Baik</option>
                        <option value="rusak">Rusak</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm font-bold hover:bg-teal-700 transition">Simpan</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-100">
                        <th class="py-4 px-6">#</th>
                        <th class="py-4 px-6">Nomor Seri</th>
                        <th class="py-4 px-6">Kondisi</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm text-slate-600">
                    @forelse($item->units as $i => $unit)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-4 px-6 text-slate-400 font-medium">{{ $i + 1 }}</td>
                            <td class="py-4 px-6 font-bold text-slate-800">{{ $unit->serial_number }}</td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $unit->condition === 'baik' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                    {{ ucfirst($unit->condition) }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                @if($unit->status === 'tersedia')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Tersedia</span>
                                @elseif($unit->status === 'dipinjam')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">Dipinjam</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">Maintenance</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @if($unit->status !== 'dipinjam')
                                    <div class="flex gap-2">
                                        <form action="{{ route('admin.inventory.units.update', $unit) }}" method="POST" class="inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="condition" value="{{ $unit->condition === 'baik' ? 'rusak' : 'baik' }}">
                                            <input type="hidden" name="status" value="{{ $unit->status === 'maintenance' ? 'tersedia' : 'maintenance' }}">
                                            <button type="submit" class="px-2.5 py-1 bg-slate-50 text-slate-600 rounded-lg text-xs font-bold hover:bg-slate-100 border border-slate-100 transition" title="Toggle Status">
                                                ⟳
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.inventory.units.delete', $unit) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus unit ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 bg-red-50 text-red-600 rounded-lg text-xs font-bold hover:bg-red-100 border border-red-100 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">Sedang dipinjam</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 px-6 text-center text-slate-400">Belum ada unit terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full" @click.away="showDeleteModal = false">
            <h3 class="text-lg font-bold text-slate-800 mb-2">Hapus Barang?</h3>
            <p class="text-sm text-slate-500 mb-4">Tindakan ini akan menghapus <b>{{ $item->name }}</b> beserta seluruh unit-nya. Tidak bisa dibatalkan.</p>
            <div class="flex gap-3">
                <form action="{{ route('admin.inventory.destroy', $item) }}" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-2.5 bg-red-600 text-white rounded-xl font-bold text-sm hover:bg-red-700 transition">Ya, Hapus</button>
                </form>
                <button @click="showDeleteModal = false" class="flex-1 py-2.5 bg-slate-100 text-slate-600 rounded-xl font-bold text-sm hover:bg-slate-200 transition">Batal</button>
            </div>
        </div>
    </div>
</div>
@endsection
