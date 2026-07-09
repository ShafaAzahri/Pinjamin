<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\WebApiController;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Fine;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoanController extends WebApiController
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'menunggu_persetujuan');

        $response = $this->callApi('GET', '/api/admin/loans', ['status' => $status]);
        
        $loansData = $response['data'] ?? [];
        $loans = Loan::whereIn('id', collect($loansData)->pluck('id'))
            ->with(['user', 'loanItems.unit.item', 'approvedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

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

    public function show(Loan $loan)
    {
        $loan->load(['user', 'loanItems.unit.item', 'fines', 'approvedBy']);
        return view('admin.loans.show', compact('loan'));
    }

    public function approve(Loan $loan)
    {
        $response = $this->callApi('POST', "/api/admin/loans/{$loan->id}/approve");

        if (isset($response['message']) && str_contains($response['message'], 'berhasil')) {
            return back()->with('success', $response['message']);
        }

        return back()->with('error', $response['message'] ?? 'Gagal menyetujui peminjaman.');
    }

    public function reject(Request $request, Loan $loan)
    {
        $response = $this->callApi('POST', "/api/admin/loans/{$loan->id}/reject", [
            'reason' => $request->input('rejection_reason')
        ]);

        if (isset($response['message']) && str_contains($response['message'], 'berhasil')) {
            return back()->with('success', $response['message']);
        }

        return back()->with('error', $response['message'] ?? 'Gagal menolak peminjaman.');
    }

    public function verifyReturn(Request $request, Loan $loan)
    {
        $request->validate([
            'unit_conditions'   => 'required|array',
            'unit_conditions.*' => 'required|in:baik,rusak',
        ]);

        $action = 'approve'; // Default web action adalah approve
        $response = $this->callApi('POST', "/api/admin/loans/{$loan->id}/verify-return", [
            'action'          => $action,
            'unit_conditions' => $request->input('unit_conditions')
        ]);

        if (isset($response['message']) && str_contains($response['message'], 'berhasil')) {
            return back()->with('success', 'Pengembalian berhasil diverifikasi!');
        }

        return back()->with('error', $response['message'] ?? 'Gagal memverifikasi pengembalian.');
    }

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
