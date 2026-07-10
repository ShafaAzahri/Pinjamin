@extends('layouts.student')

@section('title', 'Profil Saya')

@section('content')
<div class="max-w-2xl mx-auto space-y-8" x-data="{ activeMenu: 'pribadi' }">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Profil Saya</h2>
        <p class="text-sm text-slate-500 mt-1">Kelola data akun dan foto identitas KTM Anda</p>
    </div>

    <!-- Profile Header Card -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 flex items-center gap-5">
        <div class="h-16 w-16 rounded-full bg-teal-100 flex items-center justify-center text-teal-700 font-extrabold text-xl uppercase border border-white shadow-sm shrink-0">
            @if($user->profile_photo)
                <img src="{{ asset('storage/' . $user->profile_photo) }}" class="h-full w-full rounded-full object-cover">
            @else
                {{ substr($user->name, 0, 2) }}
            @endif
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="font-extrabold text-slate-800 text-lg leading-tight truncate">{{ $user->name }}</h3>
            <p class="text-xs text-slate-400 mt-1 font-semibold truncate">{{ $user->nim ?? '-' }} · {{ $user->prodi ?? '-' }}</p>
        </div>
        <div>
            @if($user->status === 'aktif')
                <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Aktif
                </span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                    Belum Aktif
                </span>
            @endif
        </div>
    </div>

    <!-- List-Style Settings Menus -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden divide-y divide-slate-100">
        
        <!-- Menu 1: Informasi Pribadi -->
        <div class="p-6">
            <button @click="activeMenu = activeMenu === 'pribadi' ? '' : 'pribadi'" 
                class="w-full flex items-center justify-between text-left font-bold text-slate-700 hover:text-teal-600 transition">
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-sm font-bold text-slate-800 block">Informasi Pribadi</span>
                        <span class="text-[10px] text-slate-400 font-semibold">Nama, email, WhatsApp, dan foto profil</span>
                    </div>
                </div>
                <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" :class="activeMenu === 'pribadi' ? 'rotate-180 text-teal-600' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Expandable Form 1 -->
            <div x-show="activeMenu === 'pribadi'" x-transition class="mt-6 pt-6 border-t border-slate-50 space-y-4">
                <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required 
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alamat Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required 
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nomor WhatsApp (Aktif)</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="e.g. 0895xxx"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Foto Profil</label>
                            <input type="file" name="profile_photo" accept="image/*" 
                                class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 file:transition">
                        </div>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-5 py-2.5 bg-teal-600 text-white rounded-2xl text-xs font-bold hover:bg-teal-700 shadow-md shadow-teal-600/10 transition">
                            Simpan Informasi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Menu 2: Kartu KTM / Identitas -->
        <div class="p-6">
            <button @click="activeMenu = activeMenu === 'ktm' ? '' : 'ktm'" 
                class="w-full flex items-center justify-between text-left font-bold text-slate-700 hover:text-teal-600 transition">
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.378 0 2.5-1.122 2.5-2.5S10.378 8 9 8M9 14a3 3 0 00-3 3h6a3 3 0 00-3-3z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-sm font-bold text-slate-800 block">Kartu KTM / Identitas</span>
                        <span class="text-[10px] text-slate-400 font-semibold">Foto KTM untuk prasyarat peminjaman alat</span>
                    </div>
                </div>
                <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" :class="activeMenu === 'ktm' ? 'rotate-180 text-teal-600' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Expandable Form 2 -->
            <div x-show="activeMenu === 'ktm'" x-transition class="mt-6 pt-6 border-t border-slate-50 space-y-5">
                <!-- Current KTM view -->
                <div class="space-y-3">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Foto KTM Saat Ini</span>
                    @if($user->ktm_photo)
                        <div class="rounded-2xl overflow-hidden border border-slate-100 bg-slate-50 relative aspect-[1.6/1] max-w-sm mx-auto shadow-sm">
                            <img src="{{ asset('storage/' . $user->ktm_photo) }}" class="h-full w-full object-cover">
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-slate-400 text-xs max-w-sm mx-auto">
                            Belum mengunggah foto KTM
                        </div>
                    @endif
                </div>

                @if($user->status !== 'aktif' || !$user->ktm_photo)
                    <form action="{{ route('student.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4 pt-2"
                        x-data="{ 
                            imageUrl: null,
                            fileName: '',
                            fileChosen(event) {
                                const file = event.target.files[0];
                                if (file) {
                                    this.fileName = file.name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        this.imageUrl = e.target.result;
                                    };
                                    reader.readAsDataURL(file);
                                } else {
                                    this.imageUrl = null;
                                    this.fileName = '';
                                }
                            }
                        }">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Unggah Foto KTM Baru</label>
                            
                            <label for="ktm_photo_profile" class="mt-1 flex flex-col justify-center items-center px-6 pt-5 pb-6 border-2 border-slate-200 border-dashed rounded-2xl hover:border-teal-500 transition-all bg-slate-50/50 cursor-pointer min-h-[160px] relative">
                                <input id="ktm_photo_profile" name="ktm_photo" type="file" accept="image/*" class="sr-only" required @change="fileChosen">
                                
                                <div x-show="imageUrl" class="absolute inset-0 p-2 flex flex-col items-center justify-center bg-white rounded-2xl z-10" style="display: none;">
                                    <img :src="imageUrl" class="max-h-[120px] rounded-xl object-contain shadow-sm">
                                    <p class="text-xs text-slate-500 mt-2 font-semibold" x-text="fileName"></p>
                                    <span class="text-[10px] text-teal-600 hover:underline mt-1 font-bold">Ubah foto KTM</span>
                                </div>

                                <div x-show="!imageUrl" class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-slate-600 justify-center">
                                        <span class="font-bold text-teal-700 hover:text-teal-800">Unggah berkas foto KTM</span>
                                    </div>
                                    <p class="text-xs text-slate-400">PNG, JPG, JPEG sampai dengan 2MB</p>
                                </div>
                            </label>
                            
                            <p class="text-[10px] text-slate-400 font-semibold mt-0.5">Unggah ulang jika KTM sebelumnya buram atau ditolak Admin.</p>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-5 py-2.5 bg-teal-600 text-white rounded-2xl text-xs font-bold hover:bg-teal-700 shadow-md shadow-teal-600/10 transition">
                                Unggah KTM
                            </button>
                        </div>
                    </form>
                @else
                    <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100 text-center flex items-center justify-center gap-2 max-w-sm mx-auto mt-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                        <span class="text-xs font-bold text-emerald-800">KTM Terverifikasi & Foto Terkunci</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Menu 3: Keamanan Akun (Password) -->
        <div class="p-6">
            <button @click="activeMenu = activeMenu === 'password' ? '' : 'password'" 
                class="w-full flex items-center justify-between text-left font-bold text-slate-700 hover:text-teal-600 transition">
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-sm font-bold text-slate-800 block">Keamanan Akun</span>
                        <span class="text-[10px] text-slate-400 font-semibold">Ubah password masuk Anda</span>
                    </div>
                </div>
                <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" :class="activeMenu === 'password' ? 'rotate-180 text-teal-600' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Expandable Form 3 -->
            <div x-show="activeMenu === 'password'" x-transition class="mt-6 pt-6 border-t border-slate-50 space-y-4">
                <form action="{{ route('student.profile.update') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Password Baru</label>
                            <input type="password" name="password" placeholder="••••••••" required 
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" placeholder="••••••••" required 
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 transition">
                        </div>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-5 py-2.5 bg-teal-600 text-white rounded-2xl text-xs font-bold hover:bg-teal-700 shadow-md shadow-teal-600/10 transition">
                            Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
