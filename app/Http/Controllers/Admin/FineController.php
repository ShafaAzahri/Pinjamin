<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FineController extends Controller
{
    /**
     * Display all fines with status filter.
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'belum_dibayar');

        $query = Fine::with(['loan.user']);

        if ($status !== 'semua') {
            $query->where('status', $status);
        }

        $fines = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $counts = [
            'belum_dibayar'        => Fine::where('status', 'belum_dibayar')->count(),
            'menunggu_verifikasi'  => Fine::where('status', 'menunggu_verifikasi')->count(),
            'lunas'                => Fine::where('status', 'lunas')->count(),
        ];

        $totalUnpaid = Fine::where('status', 'belum_dibayar')->sum('amount');

        return view('admin.fines.index', compact('fines', 'status', 'counts', 'totalUnpaid'));
    }

    /**
     * Verify fine payment proof.
     */
    public function verifyPayment(Request $request, Fine $fine)
    {
        if ($fine->status !== 'menunggu_verifikasi') {
            return back()->with('error', 'Denda ini tidak dalam status menunggu verifikasi.');
        }

        $action = $request->input('action');

        if ($action === 'approve') {
            $fine->update([
                'status'      => 'lunas',
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

            Notification::create([
                'user_id' => $fine->loan->user_id,
                'title'   => 'Pembayaran Denda Diverifikasi',
                'message' => 'Pembayaran denda Anda sebesar Rp ' . number_format($fine->amount, 0, ',', '.') . ' telah diverifikasi. Terima kasih!',
            ]);

            return back()->with('success', 'Pembayaran denda berhasil diverifikasi!');
        } elseif ($action === 'reject') {
            $fine->update([
                'status'             => 'belum_dibayar',
                'payment_proof_photo' => null,
            ]);

            Notification::create([
                'user_id' => $fine->loan->user_id,
                'title'   => 'Bukti Pembayaran Ditolak',
                'message' => 'Bukti pembayaran denda Anda ditolak. Silakan unggah ulang bukti pembayaran yang valid.',
            ]);

            return back()->with('success', 'Bukti pembayaran ditolak. Mahasiswa akan diminta mengunggah ulang.');
        }

        return back();
    }
}
