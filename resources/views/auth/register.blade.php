<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Mahasiswa - Pinjamin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-full flex items-center justify-center font-sans antialiased py-12">
    <div class="w-full max-w-lg p-6">
        <!-- Brand logo / header -->
        <div class="flex flex-col items-center mb-8">
            <img src="{{ asset('images/pinjamin-logo.png') }}" alt="PINJAMIN Logo" class="h-32 w-auto mb-2">
            <p class="text-sm text-slate-500 mt-1">Daftar Akun Peminjaman Lab Elektro</p>
        </div>

        <!-- Register Card -->
        <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-slate-100 shadow-xl shadow-slate-100/40 p-8">
            <h2 class="text-xl font-bold text-slate-800 mb-6">Pendaftaran Akun Baru</h2>

            @if($errors->any())
                <div class="mb-5 p-4 rounded-2xl bg-red-50 border border-red-100 text-red-800 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="/register" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required 
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 transition duration-200 text-slate-800 bg-slate-50/50"
                        placeholder="Nama Lengkap sesuai KTM">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="nim" class="block text-sm font-semibold text-slate-700 mb-2">NIM</label>
                        <input type="text" id="nim" name="nim" value="{{ old('nim') }}" required 
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 transition duration-200 text-slate-800 bg-slate-50/50"
                            placeholder="Contoh: 3.32.22.0.12">
                    </div>
                    <div>
                        <label for="prodi" class="block text-sm font-semibold text-slate-700 mb-2">Program Studi</label>
                        <input type="text" id="prodi" name="prodi" value="{{ old('prodi') }}" required 
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 transition duration-200 text-slate-800 bg-slate-50/50"
                            placeholder="Contoh: Teknik Informatika">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Alamat Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required 
                        class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 transition duration-200 text-slate-800 bg-slate-50/50"
                        placeholder="nama@student.polines.ac.id">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                        <input type="password" id="password" name="password" required 
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 transition duration-200 text-slate-800 bg-slate-50/50"
                            placeholder="Min 8 karakter">
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-2">Ulangi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required 
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 focus:outline-none focus:border-teal-600 focus:ring-4 focus:ring-teal-500/10 transition duration-200 text-slate-800 bg-slate-50/50"
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
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Foto KTM (Kartu Tanda Mahasiswa)</label>
                    <label for="ktm_photo" class="mt-1 flex flex-col justify-center items-center px-6 pt-5 pb-6 border-2 border-slate-200 border-dashed rounded-2xl hover:border-teal-500 transition-all bg-slate-50/50 cursor-pointer min-h-[160px] relative">
                        <input id="ktm_photo" name="ktm_photo" type="file" accept="image/*" class="sr-only" required @change="fileChosen">
                        
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
                                <span class="font-bold text-teal-700 hover:text-teal-800">Unggah berkas foto</span>
                                <p class="pl-1">atau seret dan taruh</p>
                            </div>
                            <p class="text-xs text-slate-400">PNG, JPG, JPEG sampai dengan 2MB</p>
                        </div>
                    </label>
                </div>

                <button type="submit" 
                    class="w-full py-3.5 px-4 bg-teal-700 hover:bg-teal-800 active:scale-[0.98] text-white font-bold rounded-2xl shadow-lg shadow-teal-700/20 transition-all duration-200 mt-2">
                    Daftar Akun
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-slate-500 mt-8">
            Sudah punya akun? 
            <a href="/login" class="font-bold text-teal-700 hover:underline">Masuk disini</a>
        </p>
    </div>
    <x-page-loader />
</body>
</html>
