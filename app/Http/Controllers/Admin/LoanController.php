<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\ItemUnit;
use App\Models\Fine;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoanController extends Controller
{
    /**
     * Display all loans with tab filtering.
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'menunggu_persetujuan');

        $query = Loan::with(['user', 'loanItems.unit.item', 'approvedBy']);

        if ($status !== 'semua') {
            $query->where('status', $status);
        }

        $loans = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Loan status count badges
        $counts = [
            'menunggu_persetujuan'          => Loan::where('status', 'menunggu_persetujuan')->count(),
            'aktif'                          => Loan::where('status', 'aktif')->count(),
            'menunggu_verifikasi_kembali'   => Loan::where('status', 'menunggu_verifikasi_kembali')->count(),
            'terlambat'                      => Loan::where('status', 'terlambat')->count(),
            'selesai'                        => Loan::where('status', 'selesai')->count(),
            'ditolak'                        => Loan::where('status', 'ditolak')->count(),
        ];

        return view('admin.loans.index', compact('loans', 'status', 'counts'));
    }

    /**
     * Show loan detail.
     */
    public function show(Loan $loan)
    {
        $loan->load(['user', 'loanItems.unit.item', 'fines', 'approvedBy']);
        return view('admin.loans.show', compact('loan'));
    }

    /**
     * Approve a pending loan request.
     */
    public function approve(Loan $loan)
    {
        if ($loan->status !== 'menunggu_persetujuan') {
            return back()->with('error', 'Peminjaman ini tidak dalam status menunggu persetujuan.');
        }

        // Check all requested units are still available
        foreach ($loan->loanItems as $loanItem) {
            if ($loanItem->unit->status !== 'tersedia') {
                return back()->with('error', "Unit {$loanItem->unit->serial_number} sudah tidak tersedia.");
            }
        }

        DB::transaction(function () use ($loan) {
            // Update loan
            $loan->update([
                'status'      => 'aktif',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Mark all units as borrowed
            foreach ($loan->loanItems as $loanItem) {
                $loanItem->unit->update(['status' => 'dipinjam']);
            }

            // Notify student
            Notification::create([
                'user_id' => $loan->user_id,
                'title'   => 'Peminjaman Disetujui',
                'message' => "Peminjaman Anda (ID: L" . str_pad($loan->id, 3, '0', STR_PAD_LEFT) . ") telah disetujui. Silakan ambil barang di Lab.",
            ]);
        });

        return back()->with('success', 'Peminjaman berhasil disetujui!');
    }

    /**
     * Reject a pending loan request.
     */
    public function reject(Request $request, Loan $loan)
    {
        if ($loan->status !== 'menunggu_persetujuan') {
            return back()->with('error', 'Peminjaman ini tidak dalam status menunggu persetujuan.');
        }

        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $loan->update(['status' => 'ditolak']);

        $reason = $request->input('rejection_reason', 'Tidak ada alasan yang diberikan.');

        Notification::create([
            'user_id' => $loan->user_id,
            'title'   => 'Peminjaman Ditolak',
            'message' => "Peminjaman Anda (ID: L" . str_pad($loan->id, 3, '0', STR_PAD_LEFT) . ") ditolak. Alasan: {$reason}",
        ]);

        return back()->with('success', 'Peminjaman berhasil ditolak.');
    }

    /**
     * Verify a return request (Task 9).
     */
    public function verifyReturn(Request $request, Loan $loan)
    {
        if ($loan->status !== 'menunggu_verifikasi_kembali') {
            return back()->with('error', 'Peminjaman ini tidak dalam status menunggu verifikasi pengembalian.');
        }

        $request->validate([
            'unit_conditions'   => 'required|array',
            'unit_conditions.*' => 'required|in:baik,rusak',
        ]);

        DB::transaction(function () use ($request, $loan) {
            $totalFine = 0;
            $finePerHour = (int) (Setting::where('key', 'fine_per_hour')->first()?->value ?? 5000);
            $maxDuration = (int) (Setting::where('key', 'max_loan_duration')->first()?->value ?? 8);

            // Calculate overdue hours
            $approvedAt = Carbon::parse($loan->approved_at);
            $returnedAt = now();
            $deadline = $approvedAt->copy()->addHours($maxDuration);
            $overdueHours = 0;

            if ($returnedAt->greaterThan($deadline)) {
                $overdueHours = (int) ceil(abs($returnedAt->diffInMinutes($deadline)) / 60);
                $totalFine += $overdueHours * $finePerHour;
            }

            // Process each unit
            $unitConditions = $request->input('unit_conditions');
            foreach ($loan->loanItems as $loanItem) {
                $newCondition = $unitConditions[$loanItem->id] ?? 'baik';
                $loanItem->update(['return_condition' => $newCondition]);

                // Mark unit as available or maintenance
                $loanItem->unit->update([
                    'status'    => $newCondition === 'rusak' ? 'maintenance' : 'tersedia',
                    'condition' => $newCondition,
                ]);

                // Add damage fine
                if ($newCondition === 'rusak') {
                    Fine::create([
                        'loan_id' => $loan->id,
                        'amount'  => 50000, // Fixed damage penalty
                        'type'    => 'kerusakan_barang',
                        'status'  => 'belum_dibayar',
                    ]);
                }
            }

            // Add lateness fine if overdue
            if ($totalFine > 0) {
                Fine::create([
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

            // Notification
            $msg = "Pengembalian Anda (ID: L" . str_pad($loan->id, 3, '0', STR_PAD_LEFT) . ") telah diverifikasi.";
            if ($totalFine > 0 || Fine::where('loan_id', $loan->id)->where('type', 'kerusakan_barang')->exists()) {
                $msg .= " Terdapat denda yang harus dibayarkan. Silakan cek halaman denda.";
            }

            Notification::create([
                'user_id' => $loan->user_id,
                'title'   => 'Pengembalian Diverifikasi',
                'message' => $msg,
            ]);
        });

        return back()->with('success', 'Pengembalian berhasil diverifikasi!');
    }

    /**
     * Generate report page for printing/saving to PDF.
     */
    public function report(Request $request)
    {
        $status = $request->input('status');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Loan::with(['user', 'loanItems.unit.item', 'fines']);

        if ($status && $status !== 'semua') {
            $query->where('status', $status);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $loans = $query->orderBy('created_at', 'desc')->get();

        return view('admin.loans.report', compact('loans', 'status', 'startDate', 'endDate'));
    }
}
