@extends('layouts.admin')

@section('title', 'Hubungkan ke Whatsapp')

@section('content')
<div class="w-full space-y-6" x-data="whatsappConnector()">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Hubungkan ke Whatsapp</h2>
        <p class="text-sm text-slate-500 mt-1">Kelola koneksi sistem notifikasi otomatis WhatsApp</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Main Connection Card -->
        <div class="md:col-span-2 bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex flex-col justify-between min-h-[380px]">
            <div class="px-6 py-4 border-b border-slate-50 bg-slate-50/50 flex items-center gap-3">
                <div class="p-2 bg-teal-100 rounded-lg text-teal-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-700">Status Koneksi Gateway</h3>
            </div>

            <!-- Content Body based on state -->
            <div class="p-8 flex-1 flex flex-col items-center justify-center text-center">
                <!-- State 1: Loading -->
                <div x-show="state === 'loading'" class="space-y-3">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-teal-600 mx-auto"></div>
                    <p class="text-sm font-medium text-slate-500">Menghubungkan ke API server...</p>
                </div>

                <!-- State 2: Offline -->
                <div x-show="state === 'offline'" class="space-y-4" style="display: none;">
                    <div class="h-16 w-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto shadow-inner">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-slate-800">Gateway Offline</h4>
                        <p class="text-xs text-slate-400 max-w-sm mt-1 mx-auto">Sistem belum terhubung ke WhatsApp Gateway server.</p>
                    </div>
                    <form action="{{ route('admin.whatsapp.start') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white text-sm font-bold rounded-2xl shadow-lg shadow-emerald-600/20 transition duration-150 flex items-center gap-2 mx-auto">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856a9 9 0 0113.788 0m-16.608-3.18a12.75 12.75 0 0119.425 0M12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z"/>
                            </svg>
                            Hubungkan ke Whatsapp
                        </button>
                    </form>
                </div>

                <!-- State 3: Disconnected / Scan QR -->
                <div x-show="state === 'scan'" class="space-y-4" style="display: none;">
                    <div>
                        <h4 class="text-base font-bold text-slate-800">Scan QR Code</h4>
                        <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto">Buka WhatsApp di HP Anda > Perangkat Tertaut (Linked Devices) > Pindai QR Code di bawah ini:</p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-3xl border border-slate-100 inline-block shadow-inner">
                        <template x-if="qrUrl">
                            <img :src="qrUrl" alt="WhatsApp QR Code" class="h-48 w-48 mx-auto object-contain">
                        </template>
                        <template x-if="!qrUrl">
                            <div class="h-48 w-48 flex items-center justify-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-teal-600"></div>
                            </div>
                        </template>
                    </div>
                    <p class="text-[10px] text-amber-600 font-bold animate-pulse">Menunggu pemindaian dari ponsel Anda...</p>
                </div>

                <!-- State 4: Connected -->
                <div x-show="state === 'connected'" class="space-y-4" style="display: none;">
                    <div class="h-20 w-20 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto shadow-md border border-emerald-100">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-base font-black text-slate-800">WhatsApp Aktif & Siap!</h4>
                        <p class="text-xs text-slate-500 max-w-sm mt-1 mx-auto">Sistem notifikasi Pinjamin saat ini terhubung ke nomor WhatsApp Anda secara penuh.</p>
                    </div>
                    <button type="button" @click="disconnect()" 
                        class="px-5 py-2.5 bg-red-50 hover:bg-red-100 active:scale-[0.98] text-red-700 text-xs font-bold rounded-xl border border-red-200 transition duration-150 flex items-center gap-2 mx-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Putuskan Koneksi (Logout)
                    </button>
                </div>
            </div>
        </div>

        <!-- Info Sidebar Card -->
        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-4">
            <h3 class="font-bold text-slate-800 text-sm">Informasi Notifikasi</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Notifikasi WhatsApp digunakan secara otomatis oleh sistem Pinjamin untuk mengirimkan pemberitahuan kepada mahasiswa terkait:
            </p>
            <ul class="space-y-2 text-xs text-slate-600">
                <li class="flex items-center gap-2 font-medium">
                    <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                    Penolakan / Penerimaan Pendaftaran Akun
                </li>
                <li class="flex items-center gap-2 font-medium">
                    <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                    Pemberitahuan Peminjaman Barang Disetujui
                </li>
                <li class="flex items-center gap-2 font-medium">
                    <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                    Tagihan Denda Keterlambatan
                </li>
                <li class="flex items-center gap-2 font-medium">
                    <span class="h-1.5 w-1.5 rounded-full bg-teal-500"></span>
                    Pengingat Batas Pengembalian Barang
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
    function whatsappConnector() {
        const baseUrl = "{{ $whatsappBaseUrl }}";
        return {
            state: 'loading', // loading, offline, scan, connected
            qrUrl: null,
            intervalId: null,

            init() {
                this.checkStatus();
                // Polling status setiap 5 detik
                this.intervalId = setInterval(() => this.checkStatus(), 5000);
            },

            async checkStatus() {
                try {
                    const res = await fetch(`${baseUrl}/status`);
                    if (!res.ok) throw new Error('Network error');
                    const data = await res.json();
                    
                    if (data.isReady) {
                        this.state = 'connected';
                        this.qrUrl = null;
                    } else if (data.qr) {
                        this.state = 'scan';
                        this.qrUrl = data.qr;
                    } else {
                        // Server WA menyala tapi belum siap & QR belum terbuat
                        this.state = 'loading';
                        this.qrUrl = null;
                    }
                } catch (e) {
                    this.state = 'offline';
                    this.qrUrl = null;
                }
            },

            async disconnect() {
                if (!confirm('Apakah Anda yakin ingin mengeluarkan akun WhatsApp ini dari gateway?')) return;
                
                try {
                    const res = await fetch(`${baseUrl}/disconnect`, { method: 'POST' });
                    const data = await res.json();
                    if (data.status === 'success') {
                        this.state = 'loading';
                        this.checkStatus();
                    } else {
                        alert(data.message);
                    }
                } catch (e) {
                    alert('Gagal menghubungi API server.');
                }
            }
        }
    }
</script>
@endsection
