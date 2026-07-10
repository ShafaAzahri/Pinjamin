<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Mahasiswa - Pinjamin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script type="module" src="https://cdn.skypack.dev/@hotwired/turbo"></script>
</head>
<body class="min-h-full flex font-sans antialiased">
    
    <!-- Left: Branding (Hidden on mobile) -->
    <div class="hidden lg:flex lg:w-5/12 bg-slate-50 flex-col items-center justify-center p-12 relative overflow-hidden lg:sticky lg:top-0 lg:h-screen">
        <!-- Decoration -->
        <div class="absolute inset-0 bg-gradient-to-br from-teal-50 to-slate-100 opacity-90"></div>
        <div class="absolute -top-32 -right-32 w-96 h-96 bg-teal-200/50 rounded-full blur-3xl mix-blend-multiply"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-blue-200/50 rounded-full blur-3xl mix-blend-multiply"></div>

        <div class="relative z-10 flex flex-col items-center">
            <div class="bg-white p-8 rounded-[3rem] shadow-xl border border-white mb-8">
                <img src="{{ asset('images/pinjamin-logo.png') }}" alt="PINJAMIN Logo" class="h-48 w-auto">
            </div>
            <p class="text-slate-600 font-medium text-lg text-center max-w-sm">Sistem Peminjaman Alat Laboratorium Elektro yang Mudah, Cepat, dan Aman.</p>
        </div>
    </div>

    <!-- Right: Form -->
    <div class="w-full lg:w-7/12 flex items-center justify-center bg-white p-6 sm:p-12 min-h-screen shadow-[-20px_0_40px_rgba(0,0,0,0.02)] z-10">
        <div class="w-full max-w-xl">
            
            <!-- Mobile Logo -->
            <div class="flex lg:hidden flex-col items-center mb-8">
                <img src="{{ asset('images/pinjamin-logo.png') }}" alt="PINJAMIN Logo" class="h-24 w-auto mb-2">
            </div>

            <h2 class="text-3xl font-black text-slate-800 mb-2 tracking-tight">Daftar Akun Baru</h2>
            <p class="text-slate-500 mb-8 font-medium">Lengkapi data diri Anda untuk menggunakan layanan peminjaman.</p>

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

            <form action="/register" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                
                <div>
                    <label for="name" class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required 
                        class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 transition duration-200 text-slate-800 bg-white placeholder-slate-400 font-medium shadow-sm"
                        placeholder="Nama Lengkap sesuai KTM">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="nim" class="block text-sm font-bold text-slate-700 mb-2">NIM</label>
                        <input type="text" id="nim" name="nim" value="{{ old('nim') }}" required 
                            class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 transition duration-200 text-slate-800 bg-white placeholder-slate-400 font-medium shadow-sm"
                            placeholder="Contoh: 3.32.22.0.12">
                    </div>
                    <div>
                        <label for="prodi" class="block text-sm font-bold text-slate-700 mb-2">Program Studi</label>
                        <input type="text" id="prodi" name="prodi" value="{{ old('prodi') }}" required 
                            class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 transition duration-200 text-slate-800 bg-white placeholder-slate-400 font-medium shadow-sm"
                            placeholder="Contoh: Teknik Informatika">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-bold text-slate-700 mb-2">Alamat Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required 
                        class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 transition duration-200 text-slate-800 bg-white placeholder-slate-400 font-medium shadow-sm"
                        placeholder="nama@student.polines.ac.id">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="password" class="block text-sm font-bold text-slate-700 mb-2">Password</label>
                        <input type="password" id="password" name="password" required 
                            class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 transition duration-200 text-slate-800 bg-white placeholder-slate-400 font-medium shadow-sm"
                            placeholder="Min 8 karakter">
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-bold text-slate-700 mb-2">Ulangi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required 
                            class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 transition duration-200 text-slate-800 bg-white placeholder-slate-400 font-medium shadow-sm"
                            placeholder="Ulangi password">
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
                            <span class="text-[10px] text-teal-600 group-hover:underline mt-1 font-bold">Ubah foto KTM</span>
                        </div>

                        <div x-show="!imageUrl" class="space-y-2 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-400 group-hover:text-teal-500 transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-slate-600 justify-center">
                                <span class="font-bold text-teal-700 group-hover:text-teal-800 transition-colors">Unggah berkas foto</span>
                                <p class="pl-1">atau seret dan taruh</p>
                            </div>
                            <p class="text-xs text-slate-400 font-medium">PNG, JPG, JPEG sampai dengan 2MB</p>
                        </div>
                    </label>
                </div>

                <button type="submit" 
                    class="w-full py-4 px-4 bg-teal-600 hover:bg-teal-700 active:bg-teal-800 text-white font-black rounded-2xl shadow-lg shadow-teal-600/30 transition-all duration-200 transform hover:-translate-y-0.5 mt-4">
                    Daftar Akun
                </button>

                <div class="relative flex items-center justify-center mt-6 mb-4">
                    <div class="absolute border-t border-slate-200 w-full"></div>
                    <span class="bg-white px-4 text-xs font-bold text-slate-400 uppercase relative z-10">Atau</span>
                </div>

                <a href="{{ route('sso.google') }}"
                    class="w-full py-3.5 px-4 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold rounded-2xl shadow-sm transition-all duration-200 flex items-center justify-center gap-3 group">
                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform text-slate-700" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z" clip-rule="evenodd"></path>
                    </svg>
                    Daftar dengan SSO
                </a>
            </form>
            
            <div class="mt-8 pt-8 border-t border-slate-100">
                <p class="text-center text-sm font-medium text-slate-500">
                    Sudah punya akun? 
                    <a href="/login" class="font-bold text-teal-600 hover:text-teal-700 hover:underline">Masuk disini</a>
                </p>
            </div>
        </div>
    </div>
    
    <x-page-loader />
</body>
</html>
