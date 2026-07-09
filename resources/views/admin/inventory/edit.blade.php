@extends('layouts.admin')

@section('title', 'Edit ' . $item->name)

@section('content')
<div class="max-w-3xl space-y-6">
    <div>
        <a href="{{ route('admin.inventory.show', $item) }}" class="text-sm text-slate-500 hover:text-teal-600 font-semibold transition">← Kembali ke Detail</a>
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-2">Edit Barang</h2>
    </div>

    <form action="{{ route('admin.inventory.update', $item) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf @method('PUT')

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
            <h3 class="text-lg font-bold text-slate-800">Informasi Barang</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Barang <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $item->name) }}" required
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition text-sm @error('name') border-red-400 @enderror">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                    <select name="category_id" required
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition text-sm">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $item->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi</label>
                <textarea name="description" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition text-sm resize-none">{{ old('description', $item->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Foto Barang</label>
                @if($item->image)
                    <div class="mb-3">
                        <img src="{{ asset('storage/' . $item->image) }}" class="h-24 rounded-xl border border-slate-100 object-cover">
                    </div>
                @endif
                <input type="file" name="image" accept="image/*"
                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-teal-50 file:text-teal-700">
                <p class="text-[10px] text-slate-400 mt-1">Kosongkan jika tidak ingin mengubah foto.</p>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-6 py-3 bg-teal-600 text-white rounded-xl font-bold text-sm hover:bg-teal-700 shadow-md shadow-teal-600/20 transition">
                Simpan Perubahan
            </button>
            <a href="{{ route('admin.inventory.show', $item) }}" class="px-6 py-3 bg-slate-100 text-slate-600 rounded-xl font-bold text-sm hover:bg-slate-200 transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
