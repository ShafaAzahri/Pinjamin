@extends('layouts.student')

@section('title', 'Notifikasi')

@section('content')
<div class="w-full space-y-6">
    <div>
        <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Notifikasi</h2>
        <p class="text-sm text-slate-500 mt-1">Informasi terbaru tentang akun dan peminjaman Anda</p>
    </div>

    <div class="space-y-3">
        @forelse($notifications as $notif)
            <div class="bg-white rounded-2xl border {{ $notif->is_read ? 'border-slate-100' : 'border-teal-200 bg-teal-50/30' }} shadow-sm p-5 transition hover:shadow-md">
                <div class="flex items-start gap-4">
                    <div class="h-10 w-10 rounded-xl {{ $notif->is_read ? 'bg-slate-100 text-slate-400' : 'bg-teal-100 text-teal-600' }} flex items-center justify-center shrink-0">
                        @if(str_contains($notif->title, 'Disetujui') || str_contains($notif->title, 'Diverifikasi') || str_contains($notif->title, 'Berhasil'))
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif(str_contains($notif->title, 'Ditolak'))
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start">
                            <h3 class="font-bold text-slate-800 text-sm">{{ $notif->title }}</h3>
                            <span class="text-[10px] text-slate-400 font-medium shrink-0 ml-3">{{ $notif->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-slate-600 mt-1 leading-relaxed">{{ $notif->message }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16 bg-white rounded-2xl border border-slate-100 shadow-sm">
                <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <p class="text-slate-400 font-semibold">Belum ada notifikasi.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
@endsection
