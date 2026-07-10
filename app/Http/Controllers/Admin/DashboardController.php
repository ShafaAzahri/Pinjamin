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
        
        if ($request->input('action') === 'reject') {
            $reason = $request->input('reason', 'Data tidak sesuai atau buram.');
            
            // Kirim WhatsApp sebelum user dihapus (jika ada nomor HP)
            if ($user->phone) {
                $message = "Halo *{$user->name}*,\n\n"
                         . "Mohon maaf, pendaftaran akun Pinjamin Anda *DITOLAK* oleh Admin.\n\n"
                         . "Alasan: _{$reason}_\n\n"
                         . "Silakan mendaftar ulang menggunakan data dan foto KTM yang benar. Terima kasih!";
                \App\Services\WhatsAppService::sendMessage($user->phone, $message);
            }

            // Panggil endpoint API Admin secara internal untuk logging (opsional) atau langsung hapus
            // Karena kita butuh custom message, kita hapus manual saja
            $user->delete(); 
            return back()->with('success', "Pendaftaran mahasiswa {$user->name} ditolak dan notifikasi penolakan telah dikirim ke WA.");
        }

        // Panggil endpoint API Admin secara internal untuk approve
        $response = $this->callApi('POST', "/api/admin/users/{$user->id}/verify", [
            'action' => 'approve'
        ]);

        return back()->with('success', $response['message'] ?? 'Status berhasil diubah.');
    }
}
