<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Fine;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    /**
     * Display student's loan history.
     */
    public function index()
    {
        $loans = Loan::where('user_id', Auth::id())
            ->with(['loanItems.unit.item', 'fines'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('student.loans', compact('loans'));
    }

    /**
     * Show loan detail.
     */
    public function show(Loan $loan)
    {
        // Security: only allow viewing own loans
        if ($loan->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $loan->load(['loanItems.unit.item', 'fines']);
        return view('student.loan-detail', compact('loan'));
    }

    /**
     * Submit return request with photo proof for each item.
     */
    public function submitReturn(Request $request, Loan $loan)
    {
        if ($loan->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        if (!in_array($loan->status, ['aktif', 'terlambat'])) {
            return back()->with('error', 'Peminjaman ini tidak dapat dikembalikan.');
        }

        $request->validate([
            'return_photos'   => 'required|array',
            'return_photos.*' => 'required|file|max:2048',
        ], [
            'return_photos.required'   => 'Foto bukti pengembalian wajib diunggah.',
            'return_photos.*.required' => 'Foto bukti untuk setiap item wajib diunggah.',
        ]);

        foreach ($loan->loanItems as $loanItem) {
            if ($request->hasFile("return_photos.{$loanItem->id}")) {
                $path = $request->file("return_photos.{$loanItem->id}")
                    ->store('return_proofs', 'public');
                $loanItem->update(['return_proof_photo' => $path]);
            }
        }

        $loan->update(['status' => 'menunggu_verifikasi_kembali']);

        return back()->with('success', 'Permintaan pengembalian berhasil dikirim! Menunggu verifikasi Admin.');
    }

    /**
     * Student's notifications page.
     */
    public function notifications()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Mark all as read
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('student.notifications', compact('notifications'));
    }

    /**
     * Student's fines page.
     */
    public function fines()
    {
        $fines = Fine::whereHas('loan', function ($q) {
            $q->where('user_id', Auth::id());
        })->with('loan')->orderBy('created_at', 'desc')->paginate(10);

        return view('student.fines', compact('fines'));
    }

    /**
     * Upload payment proof for a fine.
     */
    public function uploadPaymentProof(Request $request, Fine $fine)
    {
        // Security: verify fine belongs to this user
        if ($fine->loan->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        if ($fine->status !== 'belum_dibayar') {
            return back()->with('error', 'Denda ini tidak dalam status belum dibayar.');
        }

        $request->validate([
            'payment_proof' => 'required|file|max:2048',
        ], [
            'payment_proof.required' => 'Bukti pembayaran wajib diunggah.',
        ]);

        $path = $request->file('payment_proof')->store('payment_proofs', 'public');

        $fine->update([
            'payment_proof_photo' => $path,
            'status'              => 'menunggu_verifikasi',
        ]);

        return back()->with('success', 'Bukti pembayaran berhasil diunggah! Menunggu verifikasi Admin.');
    }
}
