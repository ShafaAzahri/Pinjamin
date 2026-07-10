<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\WebApiController;
use App\Models\Loan;
use App\Models\Fine;
use Illuminate\Http\Request;

class LoanController extends WebApiController
{
    public function index()
    {
        // Panggil REST API internal
        $response = $this->callApi('GET', '/api/loans');

        // Untuk kompatibilitas pagination di Blade view, kita fetch dari DB berdasarkan ID hasil API
        $loansData = $response['data'] ?? [];
        $loans = Loan::whereIn('id', collect($loansData)->pluck('id'))
            ->with(['loanItems.unit.item', 'fines'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('student.loans', compact('loans'));
    }

    public function show(Loan $loan)
    {
        // Panggil REST API internal
        $response = $this->callApi('GET', "/api/loans/{$loan->id}");

        if (isset($response['message'])) {
            abort(403, $response['message']);
        }

        $loan->load(['loanItems.unit.item', 'fines']);
        return view('student.loan-detail', compact('loan'));
    }

    public function submitReturn(Request $request, Loan $loan)
    {
        $data = [];
        if ($request->has('return_photos')) {
            $data['return_photos'] = $request->file('return_photos');
        }

        // Panggil REST API internal
        $response = $this->callApi('POST', "/api/loans/{$loan->id}/return", $data);

        if (isset($response['message']) && str_contains($response['message'], 'berhasil')) {
            return back()->with('success', $response['message']);
        }

        return back()->with('error', $response['message'] ?? 'Gagal memproses pengembalian.');
    }

    public function notifications()
    {
        // Panggil REST API internal untuk fetch notifikasi
        $response = $this->callApi('GET', '/api/notifications');

        $notifications = \App\Models\Notification::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('student.notifications', compact('notifications'));
    }

    public function fines()
    {
        // Panggil REST API internal untuk fetch denda
        $response = $this->callApi('GET', '/api/fines');

        $fines = Fine::whereHas('loan', function ($q) {
            $q->where('user_id', auth()->id());
        })->with('loan')->orderBy('created_at', 'desc')->paginate(10);

        $runningFines = \App\Models\Loan::where('user_id', auth()->id())
            ->where('status', 'terlambat')
            ->whereNotNull('approved_at')
            ->get();

        return view('student.fines', compact('fines', 'runningFines'));
    }

    public function getSnapToken(Request $request, Fine $fine)
    {
        // Panggil REST API internal untuk generate snap token
        $response = $this->callApi('POST', "/api/fines/{$fine->id}/pay");

        if (!isset($response['snap_token'])) {
            return response()->json(['error' => $response['message'] ?? 'Gagal menghubungi payment gateway.'], 500);
        }

        return response()->json($response);
    }

    public function profile()
    {
        $user = auth()->user();
        return view('student.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $data = [];
        
        if ($request->has('name')) {
            $data['name'] = $request->input('name');
        }
        if ($request->has('email')) {
            $data['email'] = $request->input('email');
        }
        if ($request->has('phone')) {
            $data['phone'] = $request->input('phone');
        }
        if ($request->has('password')) {
            $data['password'] = $request->input('password');
        }
        if ($request->has('password_confirmation')) {
            $data['password_confirmation'] = $request->input('password_confirmation');
        }

        if ($request->hasFile('profile_photo')) {
            $data['profile_photo'] = $request->file('profile_photo');
        }

        if ($request->hasFile('ktm_photo')) {
            $data['ktm_photo'] = $request->file('ktm_photo');
        }

        $response = $this->callApi('PUT', '/api/auth/profile', $data);

        if (isset($response['message']) && (str_contains($response['message'], 'berhasil') || str_contains($response['message'], 'diperbarui'))) {
            return back()->with('success', $response['message']);
        }

        return back()->with('error', $response['message'] ?? 'Gagal memperbarui profil.');
    }
}
