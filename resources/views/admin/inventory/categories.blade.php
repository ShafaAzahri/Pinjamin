@extends('layouts.admin')

@section('title', 'Kategori')

@section('content')
<div class="space-y-6" x-data="{ showAdd: false, editId: null, editName: '', editDesc: '' }">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.inventory.index') }}" class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 text-slate-500 rounded-xl text-xs font-bold hover:bg-slate-50 hover:text-teal-600 transition shadow-sm mb-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
                </svg>
                Kembali ke Inventaris
            </a>
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-2">Kategori Barang</h2>
        </div>
        <button @click="showAdd = !showAdd" class="px-4 py-2.5 bg-teal-600 text-white rounded-xl text-sm font-bold hover:bg-teal-700 transition shadow-md shadow-teal-600/20">
            + Tambah Kategori
        </button>
    </div>

    <!-- Add Form -->
    <div x-show="showAdd" x-transition class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <form action="{{ route('admin.categories.store') }}" method="POST" class="flex gap-4 items-end">
            @csrf
            <div class="flex-1">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Kategori</label>
                <input type="text" name="name" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
            </div>
            <div class="flex-1">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi</label>
                <input type="text" name="description" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-teal-600 text-white rounded-xl text-sm font-bold hover:bg-teal-700 transition">Simpan</button>
        </form>
    </div>

    <!-- Categories List -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-500 text-[10px] font-bold uppercase tracking-wider border-b border-slate-100">
                    <th class="py-4 px-6">#</th>
                    <th class="py-4 px-6">Nama Kategori</th>
                    <th class="py-4 px-6">Deskripsi</th>
                    <th class="py-4 px-6">Jumlah Barang</th>
                    <th class="py-4 px-6">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 text-sm text-slate-600">
                @forelse($categories as $i => $cat)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-4 px-6 text-slate-400">{{ $i + 1 }}</td>
                        <td class="py-4 px-6 font-bold text-slate-800">{{ $cat->name }}</td>
                        <td class="py-4 px-6 text-slate-500">{{ $cat->description ?? '-' }}</td>
                        <td class="py-4 px-6">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-50 text-slate-600 border border-slate-100">
                                {{ $cat->items_count }} barang
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex gap-2">
                                <button @click="editId = {{ $cat->id }}; editName = '{{ addslashes($cat->name) }}'; editDesc = '{{ addslashes($cat->description ?? '') }}'"
                                    class="px-2.5 py-1 bg-teal-50 text-teal-700 rounded-lg text-xs font-bold hover:bg-teal-100 border border-teal-100 transition">
                                    Edit
                                </button>
                                @if($cat->items_count === 0)
                                    <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Yakin hapus kategori ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 bg-red-50 text-red-600 rounded-lg text-xs font-bold hover:bg-red-100 border border-red-100 transition">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Inline Edit Row -->
                    <tr x-show="editId === {{ $cat->id }}" x-transition class="bg-teal-50/50">
                        <td colspan="5" class="p-4">
                            <form action="{{ route('admin.categories.update', $cat) }}" method="POST" class="flex gap-4 items-end">
                                @csrf @method('PUT')
                                <div class="flex-1">
                                    <input type="text" name="name" x-model="editName" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                                </div>
                                <div class="flex-1">
                                    <input type="text" name="description" x-model="editDesc" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                                </div>
                                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm font-bold hover:bg-teal-700 transition">Simpan</button>
                                <button type="button" @click="editId = null" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-bold hover:bg-slate-200 transition">Batal</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 px-6 text-center text-slate-400">Belum ada kategori.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
