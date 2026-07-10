<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\Loan;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    // ────────── Dashboard ──────────

    /**
     * GET /api/admin/dashboard
     */
    public function dashboard()
    {
        return response()->json([
            'data' => [
                'total_items'            => Item::count(),
                'available_units'        => ItemUnit::where('status', 'tersedia')->count(),
                'active_loans'           => Loan::whereIn('status', ['menunggu_persetujuan', 'aktif'])->count(),
                'pending_verifications'  => User::where('status', 'menunggu_verifikasi')->count(),
                'total_unpaid_fines'     => Fine::where('status', 'belum_dibayar')->sum('amount'),
                'overdue_loans'          => Loan::where('status', 'terlambat')->count(),
            ],
        ]);
    }

    // ────────── Inventory ──────────

    /**
     * GET /api/admin/inventory
     */
    public function inventory(Request $request)
    {
        $query = Item::with(['category', 'units']);

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $items = $query->orderBy('name')->paginate(15);

        return response()->json([
            'data' => $items->map(fn($item) => [
                'id'          => $item->id,
                'name'        => $item->name,
                'description' => $item->description,
                'category'    => $item->category?->name,
                'units_count' => $item->units->count(),
                'available'   => $item->units->where('status', 'tersedia')->count(),
            ]),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'total'        => $items->total(),
            ],
        ]);
    }

    // ────────── Loans ──────────

    /**
     * GET /api/admin/loans
     */
    public function loans(Request $request)
    {
        $status = $request->input('status', 'menunggu_persetujuan');

        $loans = Loan::with(['user', 'loanItems.unit.item'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'data' => $loans->map(fn($loan) => [
                'id'     => $loan->id,
                'status' => $loan->status,
                'user'   => [
                    'id'    => $loan->user->id,
                    'name'  => $loan->user->name,
                    'nim'   => $loan->user->nim,
                    'prodi' => $loan->user->prodi,
                ],
                'items'      => $loan->loanItems->map(fn($li) => [
                    'item_name'     => $li->unit->item->name,
                    'serial_number' => $li->unit->serial_number,
                ]),
                'loan_duration_hours' => $loan->loan_duration_hours,
                'created_at'          => $loan->created_at->format('d M Y H:i'),
            ]),
            'meta' => [
                'current_page' => $loans->currentPage(),
                'last_page'    => $loans->lastPage(),
                'total'        => $loans->total(),
            ],
        ]);
    }

    /**
     * POST /api/admin/loans/{loan}/approve
     */
    public function approveLoan(Loan $loan)
    {
        if ($loan->status !== 'menunggu_persetujuan') {
            return response()->json(['message' => 'Peminjaman ini tidak dalam status menunggu persetujuan.'], 422);
        }

        $loan->update([
            'status'      => 'aktif',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        $loan->user->notifications()->create([
            'title'   => 'Peminjaman Disetujui',
            'message' => 'Peminjaman Anda telah disetujui. Silakan ambil barang di Lab.',
        ]);

        $user = $loan->user;
        if ($user && $user->phone) {
            $itemNames = $loan->loanItems->map(function ($li) {
                return ($li->unit->item->name ?? 'Barang') . ' (' . ($li->unit->serial_number ?? '-') . ')';
            })->implode(', ');

            $message = "Halo *{$user->name}*,\n\nPeminjaman alat Anda dengan ID *L" . str_pad($loan->id, 3, '0', STR_PAD_LEFT) . "* telah *DISETUJUI* oleh Admin.\n\n" .
                       "Barang: {$itemNames}\n\n" .
                       "Silakan segera mengambil barang Anda di Laboratorium.\n\nTerima kasih.";

            \App\Services\WhatsAppService::send($user->phone, $message);
        }

        foreach ($loan->loanItems as $li) {
            $li->unit->update(['status' => 'dipinjam']);
        }

        return response()->json(['message' => 'Peminjaman berhasil disetujui.']);
    }

    /**
     * POST /api/admin/loans/{loan}/reject
     */
    public function rejectLoan(Request $request, Loan $loan)
    {
        if ($loan->status !== 'menunggu_persetujuan') {
            return response()->json(['message' => 'Peminjaman ini tidak dalam status menunggu persetujuan.'], 422);
        }

        $loan->update(['status' => 'ditolak']);

        $loan->user->notifications()->create([
            'title'   => 'Peminjaman Ditolak',
            'message' => $request->input('reason', 'Permintaan peminjaman Anda ditolak oleh Admin.'),
        ]);

        $user = $loan->user;
        if ($user && $user->phone) {
            $reason = $request->input('reason', 'Permintaan peminjaman Anda ditolak oleh Admin.');
            $message = "Halo *{$user->name}*,\n\nPeminjaman alat Anda dengan ID *L" . str_pad($loan->id, 3, '0', STR_PAD_LEFT) . "* telah *DITOLAK* oleh Admin.\n\n" .
                       "Alasan: {$reason}\n\nTerima kasih.";

            \App\Services\WhatsAppService::send($user->phone, $message);
        }

        return response()->json(['message' => 'Peminjaman berhasil ditolak.']);
    }

    /**
     * POST /api/admin/loans/{loan}/verify-return
     */
    public function verifyReturn(Request $request, Loan $loan)
    {
        if ($loan->status !== 'menunggu_verifikasi_kembali') {
            return response()->json(['message' => 'Peminjaman ini tidak dalam status menunggu verifikasi kembali.'], 422);
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'unit_conditions'   => 'nullable|array',
            'unit_conditions.*' => 'required|in:baik,rusak',
        ]);

        if ($request->action === 'approve') {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $loan) {
                $totalFine = 0;
                $fineAmount = (int) (\App\Models\Setting::where('key', 'fine_amount')->first()?->value ?? 5000);
                $fineType = \App\Models\Setting::where('key', 'fine_type')->first()?->value ?? 'per_hour';

                $approvedAt = \Carbon\Carbon::parse($loan->approved_at);
                $returnedAt = now();
                
                $deadline = $approvedAt->copy();
                if ($loan->loan_duration_type === 'days') {
                    $deadline->addDays($loan->loan_duration);
                } else {
                    $deadline->addHours($loan->loan_duration);
                }

                if ($returnedAt->greaterThan($deadline)) {
                    $overdueMinutes = abs($returnedAt->diffInMinutes($deadline));
                    if ($fineType === 'per_day') {
                        $overdueUnits = (int) ceil($overdueMinutes / 1440); // 1440 minutes = 1 day
                    } else {
                        $overdueUnits = (int) ceil($overdueMinutes / 60); // default per hour
                    }
                    $totalFine += $overdueUnits * $fineAmount;
                }

                $unitConditions = $request->input('unit_conditions', []);
                foreach ($loan->loanItems as $loanItem) {
                    $newCondition = $unitConditions[$loanItem->id] ?? 'baik';
                    $loanItem->update(['return_condition' => $newCondition]);

                    $loanItem->unit->update([
                        'status'    => $newCondition === 'rusak' ? 'maintenance' : 'tersedia',
                        'condition' => $newCondition,
                    ]);

                    if ($newCondition === 'rusak') {
                        \App\Models\Fine::create([
                            'loan_id' => $loan->id,
                            'amount'  => 50000,
                            'type'    => 'kerusakan_barang',
                            'status'  => 'belum_dibayar',
                        ]);
                    }
                }

                if ($totalFine > 0) {
                    \App\Models\Fine::create([
                        'loan_id' => $loan->id,
                        'amount'  => $totalFine,
                        'type'    => 'keterlambatan',
                        'status'  => 'belum_dibayar',
                    ]);
                }

                $loan->update([
                    'status'      => 'selesai',
                    'returned_at' => $returnedAt,
                ]);

                $loan->user->notifications()->create([
                    'title'   => 'Pengembalian Dikonfirmasi',
                    'message' => 'Pengembalian barang Anda telah berhasil dikonfirmasi oleh Admin.',
                ]);
            });
        } else {
            $loan->update(['status' => 'aktif']);
            $loan->user->notifications()->create([
                'title'   => 'Pengembalian Ditolak',
                'message' => 'Foto pengembalian barang Anda tidak valid. Silakan coba kembali.',
            ]);
        }

        return response()->json(['message' => 'Status pengembalian berhasil diperbarui.']);
    }

    // ────────── Fines ──────────

    /**
     * GET /api/admin/fines
     */
    public function fines(Request $request)
    {
        $status = $request->input('status', 'belum_dibayar');

        $fines = Fine::with(['loan.user'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'data' => $fines->map(fn($fine) => [
                'id'     => $fine->id,
                'type'   => $fine->type,
                'amount' => $fine->amount,
                'status' => $fine->status,
                'user'   => [
                    'id'    => $fine->loan->user->id,
                    'name'  => $fine->loan->user->name,
                    'nim'   => $fine->loan->user->nim,
                ],
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
     * POST /api/admin/fines/{fine}/verify
     */
    public function verifyFine(Request $request, Fine $fine)
    {
        $request->validate(['action' => 'required|in:approve,reject']);

        if ($request->action === 'approve') {
            $fine->update([
                'status'      => 'lunas',
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);
            $fine->loan->user->notifications()->create([
                'title'   => 'Pembayaran Denda Diverifikasi',
                'message' => 'Pembayaran denda Anda telah berhasil diverifikasi oleh Admin.',
            ]);
        } else {
            $fine->update([
                'status'             => 'belum_dibayar',
                'payment_proof_photo'=> null,
                'snap_token'         => null,
            ]);
            $fine->loan->user->notifications()->create([
                'title'   => 'Bukti Pembayaran Ditolak',
                'message' => 'Bukti pembayaran denda Anda tidak valid. Silakan unggah ulang.',
            ]);
        }

        return response()->json(['message' => 'Status denda berhasil diperbarui.']);
    }

    // ────────── Users ──────────

    /**
     * GET /api/admin/users/pending
     */
    public function pendingUsers()
    {
        $users = User::where('status', 'menunggu_verifikasi')
            ->orderBy('created_at')
            ->paginate(15);

        return response()->json([
            'data' => $users->map(fn($u) => [
                'id'        => $u->id,
                'name'      => $u->name,
                'email'     => $u->email,
                'nim'       => $u->nim,
                'prodi'     => $u->prodi,
                'ktm_photo' => $u->ktm_photo ? asset('storage/' . $u->ktm_photo) : null,
                'created_at'=> $u->created_at->format('d M Y'),
            ]),
        ]);
    }

    /**
     * POST /api/admin/users/{user}/verify
     */
    public function verifyUser(Request $request, User $user)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'reason' => 'nullable|string'
        ]);

        if ($request->action === 'approve') {
            $user->update([
                'status' => 'aktif',
                'rejection_reason' => null
            ]);
            
            // Tambahkan Notifikasi Database
            $user->notifications()->create([
                'title' => 'Verifikasi KTM Berhasil',
                'message' => 'Selamat, Kartu Tanda Mahasiswa (KTM) Anda telah disetujui dan diverifikasi oleh Admin. Anda sekarang dapat melakukan peminjaman alat.'
            ]);

            $msg = "Akun {$user->name} berhasil diverifikasi.";
        } else {
            $reason = $request->input('reason', 'Data tidak sesuai atau buram.');
            $user->update([
                'status' => 'ditolak',
                'rejection_reason' => $reason
            ]);

            // Tambahkan Notifikasi Database
            $user->notifications()->create([
                'title' => 'Verifikasi KTM Ditolak',
                'message' => "Maaf, verifikasi KTM Anda ditolak oleh Admin. Alasan: {$reason}. Silakan unggah kembali KTM Anda dengan data yang benar."
            ]);

            $msg = "Akun {$user->name} ditolak.";
 
            // Kirim WhatsApp (jika ada nomor HP)
            if ($user->phone) {
                $message = "Halo *{$user->name}*,\n\n"
                         . "Mohon maaf, pendaftaran akun Pinjamin Anda *DITOLAK* oleh Admin.\n\n"
                         . "Alasan: _{$reason}_\n\n"
                         . "Silakan mendaftar ulang menggunakan data dan foto KTM yang benar. Terima kasih!";
                \App\Services\WhatsAppService::sendMessage($user->phone, $message);
            }
        }

        return response()->json([
            'message' => $msg,
        ]);
    }

    // ────────── Settings ──────────

    /**
     * GET /api/admin/settings
     */
    public function settings()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return response()->json(['data' => $settings]);
    }

    /**
     * PUT /api/admin/settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'max_loan_duration'      => 'sometimes|integer|min:1',
            'max_loan_duration_type' => 'sometimes|in:hours,days',
            'fine_amount'            => 'sometimes|integer|min:0',
            'fine_type'              => 'sometimes|in:per_hour,per_day',
            'max_items_borrowed'     => 'sometimes|integer|min:1',
        ]);

        foreach ($request->only(['max_loan_duration', 'max_loan_duration_type', 'fine_amount', 'fine_type', 'max_items_borrowed']) as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return response()->json(['message' => 'Pengaturan berhasil diperbarui.']);
    }
}
