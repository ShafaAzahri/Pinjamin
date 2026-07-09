<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Loan;
use App\Models\Fine;
use App\Models\ItemUnit;
use App\Models\Notification;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalLoansToday = Loan::whereDate('created_at', today())->count();
        $itemsBorrowed = ItemUnit::where('status', 'dipinjam')->count();
        $pendingFines = Fine::where('status', 'belum_dibayar')->sum('amount');
        
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

    public function verifyUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $action = $request->input('action');

        if ($action === 'approve') {
            $user->update(['status' => 'aktif']);

            // Send notification
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Akun Anda Berhasil Diverifikasi',
                'message' => 'Selamat! Akun Anda telah diverifikasi oleh Admin. Sekarang Anda dapat mengajukan peminjaman alat laboratorium.',
            ]);

            return redirect('/admin/dashboard')->with('success', "Akun mahasiswa {$user->name} berhasil diverifikasi!");
        } elseif ($action === 'reject') {
            $user->delete();
            return redirect('/admin/dashboard')->with('success', "Pendaftaran mahasiswa {$user->name} ditolak dan akun dihapus.");
        }

        return redirect('/admin/dashboard');
    }
}
