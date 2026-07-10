<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\WebApiController;
use App\Models\User;
use App\Models\Loan;
use App\Models\Fine;
use App\Models\ItemUnit;
use Illuminate\Http\Request;

class DashboardController extends WebApiController
{
    public function index()
    {
        $response = $this->callApi('GET', '/api/admin/dashboard');
        
        $totalLoansToday = Loan::whereDate('created_at', today())->count(); // fallback log
        $itemsBorrowed = ItemUnit::where('status', 'dipinjam')->count();
        $pendingFines = $response['data']['total_unpaid_fines'] ?? 0;
        
        $pendingUsers = User::where('role', 'user')
            ->where('status', 'menunggu_verifikasi')
            ->orderBy('created_at', 'desc')
            ->get();

        $activeLoans = Loan::whereIn('status', ['aktif', 'terlambat'])
            ->with(['user', 'loanItems.unit.item'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.dashboard', compact(
            'totalLoansToday', 
            'itemsBorrowed', 
            'pendingFines', 
            'pendingUsers', 
            'activeLoans'
        ));
    }

    public function verificationList(Request $request)
    {
        $pendingUsers = User::where('role', 'user')
            ->where('status', 'menunggu_verifikasi')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.verification.index', compact('pendingUsers'));
    }

    public function verifyUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Panggil endpoint API Admin secara internal
        $response = $this->callApi('POST', "/api/admin/users/{$user->id}/verify", [
            'action' => $request->input('action')
        ]);

        if ($request->input('action') === 'reject') {
            $user->delete(); // maintain original delete-on-reject behavior
            return back()->with('success', "Pendaftaran mahasiswa {$user->name} ditolak dan akun dihapus.");
        }

        return back()->with('success', $response['message'] ?? 'Status berhasil diubah.');
    }
}
