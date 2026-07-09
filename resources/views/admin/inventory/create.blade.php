@extends('layouts.admin')

@section('title', 'Tambah Barang')

@section('content')
<div class="max-w-3xl space-y-6" x-data="unitManager()">
    <div>
        <a href="{{ route('admin.inventory.index') }}" class="text-sm text-slate-500 hover:text-teal-600 font-semibold transition">← Kembali ke Inventaris</a>
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mt-2">Tambah Barang Baru</h2>
    </div>

    <form action="{{ route('admin.inventory.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Item Info Card -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
            <h3 class="text-lg font-bold text-slate-800">Informasi Barang</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Barang <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition text-sm @error('name') border-red-400 @enderror">
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                    <select name="category_id" required
                        class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition text-sm @error('category_id') border-red-400 @enderror">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi</label>
                <textarea name="description" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition text-sm resize-none">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Foto Barang</label>
                <input type="file" name="image" accept="image/*"
                    class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:outline-none text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-teal-50 file:text-teal-700">
            </div>
        </div>

        <!-- Units Card -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-800">Unit Barang</h3>
                <button type="button" @click="addUnit()" 
                    class="px-3 py-1.5 bg-teal-50 text-teal-700 rounded-lg text-xs font-bold hover:bg-teal-100 border border-teal-100 transition">
                    + Tambah Unit
                </button>
            </div>

            <template x-for="(unit, index) in units" :key="index">
                <div class="flex gap-3 items-end p-4 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Nomor Seri</label>
                        <input type="text" :name="'units[' + index + '][serial_number]'" x-model="unit.serial_number" required
                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                    </div>
                    <div class="w-36">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Kondisi</label>
                        <select :name="'units[' + index + '][condition]'" x-model="unit.condition"
                            class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                            <option value="baik">Baik</option>
                            <option value="rusak">Rusak</option>
                        </select>
                    </div>
                    <button type="button" @click="removeUnit(index)" x-show="units.length > 1"
                        class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </template>

            @error('units') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            @error('units.*.serial_number') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-6 py-3 bg-teal-600 text-white rounded-xl font-bold text-sm hover:bg-teal-700 shadow-md shadow-teal-600/20 transition">
                Simpan Barang
            </button>
            <a href="{{ route('admin.inventory.index') }}" class="px-6 py-3 bg-slate-100 text-slate-600 rounded-xl font-bold text-sm hover:bg-slate-200 transition">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
function unitManager() {
    return {
        units: [{ serial_number: '', condition: 'baik' }],
        addUnit() {
            this.units.push({ serial_number: '', condition: 'baik' });
        },
        removeUnit(index) {
            this.units.splice(index, 1);
        }
    };
}
</script>
@endsection
