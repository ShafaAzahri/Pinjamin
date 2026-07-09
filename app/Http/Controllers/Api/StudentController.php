<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    /**
     * GET /api/loans
     * Student's loan history.
     */
    public function loans(Request $request)
    {
        $loans = Loan::where('user_id', Auth::id())
            ->with(['loanItems.unit.item', 'fines'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $loans->map(fn($loan) => [
                'id'                  => $loan->id,
                'status'              => $loan->status,
                'loan_duration_hours' => $loan->loan_duration_hours,
                'created_at'          => $loan->created_at->format('d M Y H:i'),
                'approved_at'         => $loan->approved_at?->format('d M Y H:i'),
                'items'               => $loan->loanItems->map(fn($li) => [
                    'id'            => $li->id,
                    'item_name'     => $li->unit->item->name,
                    'serial_number' => $li->unit->serial_number,
                ]),
                'fines' => $loan->fines->map(fn($f) => [
                    'id'     => $f->id,
                    'type'   => $f->type,
                    'amount' => $f->amount,
                    'status' => $f->status,
                ]),
            ]),
            'meta' => [
                'current_page' => $loans->currentPage(),
                'last_page'    => $loans->lastPage(),
                'total'        => $loans->total(),
            ],
        ]);
    }

    /**
     * GET /api/loans/{loan}
     * Student's loan detail.
     */
    public function loanDetail(Loan $loan)
    {
        if ($loan->user_id !== Auth::id()) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $loan->load(['loanItems.unit.item', 'fines']);

        return response()->json([
            'data' => [
                'id'                  => $loan->id,
                'status'              => $loan->status,
                'loan_duration_hours' => $loan->loan_duration_hours,
                'created_at'          => $loan->created_at->format('d M Y H:i'),
                'approved_at'         => $loan->approved_at?->format('d M Y H:i'),
                'items'               => $loan->loanItems->map(fn($li) => [
                    'id'                 => $li->id,
                    'item_name'          => $li->unit->item->name,
                    'serial_number'      => $li->unit->serial_number,
                    'condition'          => $li->unit->condition,
                    'return_proof_photo' => $li->return_proof_photo,
                ]),
                'fines' => $loan->fines->map(fn($f) => [
                    'id'     => $f->id,
                    'type'   => $f->type,
                    'amount' => $f->amount,
                    'status' => $f->status,
                ]),
            ],
        ]);
    }

    /**
     * POST /api/loans/{loan}/return
     * Submit return request with photo proof.
     */
    public function submitReturn(Request $request, Loan $loan)
    {
        if ($loan->user_id !== Auth::id()) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if (!in_array($loan->status, ['aktif', 'terlambat'])) {
            return response()->json(['message' => 'Peminjaman ini tidak dapat dikembalikan.'], 422);
        }

        $request->validate([
            'return_photos'   => 'required|array',
            'return_photos.*' => 'required|file|max:2048',
        ]);

        foreach ($loan->loanItems as $loanItem) {
            if ($request->hasFile("return_photos.{$loanItem->id}")) {
                $path = $request->file("return_photos.{$loanItem->id}")
                    ->store('return_proofs', 'public');
                $loanItem->update(['return_proof_photo' => $path]);
            }
        }

        $loan->update(['status' => 'menunggu_verifikasi_kembali']);

        return response()->json(['message' => 'Permintaan pengembalian berhasil dikirim! Menunggu verifikasi Admin.']);
    }

    /**
     * GET /api/fines
     * Student's fines.
     */
    public function fines()
    {
        $fines = Fine::whereHas('loan', function ($q) {
            $q->where('user_id', Auth::id());
        })->with('loan')->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'data' => $fines->map(fn($fine) => [
                'id'         => $fine->id,
                'loan_id'    => $fine->loan_id,
                'type'       => $fine->type,
                'amount'     => $fine->amount,
                'status'     => $fine->status,
                'snap_token' => $fine->snap_token,
                'created_at' => $fine->created_at->format('d M Y'),
            ]),
            'meta' => [
                'current_page' => $fines->currentPage(),
                'last_page'    => $fines->lastPage(),
                'total'        => $fines->total(),
            ],
        ]);
    }

    /**
     * POST /api/fines/{fine}/snap-token
     * Get Midtrans Snap Token for a fine payment.
     */
    public function getSnapToken(Fine $fine)
    {
        if ($fine->loan->user_id !== Auth::id()) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if ($fine->status !== 'belum_dibayar') {
            return response()->json(['message' => 'Denda ini tidak dalam status belum dibayar.'], 400);
        }

        if ($fine->snap_token) {
            return response()->json(['snap_token' => $fine->snap_token]);
        }

        \Midtrans\Config::$serverKey      = config('midtrans.server_key');
        \Midtrans\Config::$isProduction   = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized    = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds          = config('midtrans.is_3ds');

        $params = [
            'transaction_details' => [
                'order_id'     => 'FINE-' . $fine->id . '-' . time(),
                'gross_amount' => $fine->amount,
            ],
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email'      => Auth::user()->email,
            ],
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            $fine->update(['snap_token' => $snapToken]);
            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/notifications
     * Student's notifications.
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

        return response()->json([
            'data' => $notifications->map(fn($n) => [
                'id'         => $n->id,
                'title'      => $n->title,
                'message'    => $n->message,
                'is_read'    => (bool) $n->is_read,
                'created_at' => $n->created_at->format('d M Y H:i'),
            ]),
        ]);
    }

    /**
     * GET /api/notifications/unread-count
     * Get unread notification count.
     */
    public function unreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }
}
