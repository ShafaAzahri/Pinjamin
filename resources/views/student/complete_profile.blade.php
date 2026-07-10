<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lengkapi Profil - Pinjamin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script type="module" src="https://cdn.skypack.dev/@hotwired/turbo"></script>
</head>
<body class="min-h-full flex items-center justify-center font-sans antialiased bg-slate-50 relative py-12">
    
    <!-- Background Decor -->
    <div class="fixed -top-40 -right-40 w-96 h-96 bg-teal-200/40 rounded-full blur-3xl mix-blend-multiply pointer-events-none"></div>
    <div class="fixed -bottom-40 -left-40 w-96 h-96 bg-blue-200/40 rounded-full blur-3xl mix-blend-multiply pointer-events-none"></div>

    <div class="w-full max-w-xl p-6 relative z-10">
        <!-- Brand logo / header -->
        <div class="flex flex-col items-center mb-8">
            <div class="bg-white p-4 sm:p-5 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-white mb-4">
                <img src="{{ asset('images/pinjamin-logo.png') }}" alt="PINJAMIN Logo" class="h-28 w-auto">
            </div>
            <p class="text-sm font-medium text-slate-500 mt-1 text-center">Pendaftaran Akun Peminjaman Lab Elektro</p>
        </div>

        <!-- Card -->
        <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-slate-100 shadow-xl shadow-slate-100/40 p-8 sm:p-10">
            <h2 class="text-2xl font-black text-slate-800 mb-2 text-center tracking-tight">Lengkapi Profil Anda</h2>
            <p class="text-slate-500 mb-8 font-medium text-center text-sm">
                Halo, <strong>{{ $user->name }}</strong>! Karena Anda mendaftar melalui SSO Google, kami membutuhkan beberapa data tambahan sebelum Anda dapat meminjam alat.
            </p>

            @if($user->status === 'ditolak')
                <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-100 text-red-800 text-sm font-semibold flex items-start shadow-sm animate-pulse">
                    <svg class="w-5 h-5 mr-3 mt-0.5 shrink-0 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                    <div>
                        <span class="font-bold text-red-900">Pendaftaran Ditolak Admin:</span>
                        <p class="mt-1 text-slate-700 font-medium">"{{ $user->rejection_reason }}"</p>
                        <div class="text-[10px] text-red-600 mt-2 font-bold uppercase tracking-wider">Silakan perbarui data & unggah ulang KTM yang valid</div>
                    </div>
                </div>
            @endif

            @if(session('info'))
                <div class="mb-6 p-4 rounded-2xl bg-blue-50 border border-blue-100 text-blue-800 text-sm font-semibold flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-3 shrink-0 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ session('info') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-100 text-red-800 text-sm font-semibold flex items-start shadow-sm">
                    <svg class="w-5 h-5 mr-3 mt-0.5 shrink-0 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ url('/complete-profile') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="nim" class="block text-sm font-bold text-slate-700 mb-2">NIM</label>
                        <input type="text" id="nim" name="nim" value="{{ old('nim', $user->nim) }}" required 
                            class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 transition duration-200 text-slate-800 bg-white placeholder-slate-400 font-medium shadow-sm"
                            placeholder="Contoh: 3.32.22.0.12">
                    </div>
                    <div>
                        <label for="prodi" class="block text-sm font-bold text-slate-700 mb-2">Program Studi</label>
                        <input type="text" id="prodi" name="prodi" value="{{ old('prodi', $user->prodi) }}" required 
                            class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 transition duration-200 text-slate-800 bg-white placeholder-slate-400 font-medium shadow-sm"
                            placeholder="Contoh: Teknik Informatika">
                    </div>
                </div>

                <!-- KTM Upload -->
                <div x-data="{ 
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
                    <label class="block text-sm font-bold text-slate-700 mb-2">Foto KTM (Kartu Tanda Mahasiswa)</label>
                    <label for="ktm_photo" class="mt-1 flex flex-col justify-center items-center px-6 pt-5 pb-6 border-2 border-slate-200 border-dashed rounded-2xl hover:border-teal-500 transition-all bg-slate-50/50 cursor-pointer min-h-[160px] relative shadow-sm group">
                        <input id="ktm_photo" name="ktm_photo" type="file" accept="image/*" class="sr-only" required @change="fileChosen">
                        
                        <div x-show="imageUrl" class="absolute inset-0 p-2 flex flex-col items-center justify-center bg-white rounded-2xl z-10" style="display: none;">
                            <img :src="imageUrl" class="max-h-[120px] rounded-xl object-contain shadow-sm border border-slate-100">
                            <p class="text-xs text-slate-500 mt-2 font-semibold truncate max-w-[200px]" x-text="fileName"></p>
                        </div>

                        <div x-show="!imageUrl" class="space-y-2 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-400 group-hover:text-teal-500 transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-slate-600 justify-center">
                                <span class="font-bold text-teal-700 group-hover:text-teal-800 transition-colors">Unggah foto</span>
                                <p class="pl-1">atau seret dan taruh</p>
                            </div>
                        </div>
                    </label>
                </div>

                <button type="submit" 
                    class="w-full py-4 px-4 bg-teal-600 hover:bg-teal-700 active:bg-teal-800 text-white font-black rounded-2xl shadow-lg shadow-teal-600/30 transition-all duration-200 transform hover:-translate-y-0.5 mt-4">
                    Simpan dan Lanjutkan
                </button>
            </form>
            
            <div class="mt-8 pt-8 border-t border-slate-100">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-center text-sm font-bold text-slate-500 hover:text-red-600 transition-colors">
                        Batalkan & Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <x-page-loader />
</body>
</html>
