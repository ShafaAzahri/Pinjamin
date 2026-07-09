<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\WebApiController;
use App\Models\Fine;
use Illuminate\Http\Request;

class FineController extends WebApiController
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'belum_dibayar');

        // Fetch data via REST API
        $response = $this->callApi('GET', '/api/admin/fines', ['status' => $status]);
        
        $finesData = $response['data'] ?? [];
        $fines = Fine::whereIn('id', collect($finesData)->pluck('id'))
            ->with(['loan.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'belum_dibayar'        => Fine::where('status', 'belum_dibayar')->count(),
            'menunggu_verifikasi'  => Fine::where('status', 'menunggu_verifikasi')->count(),
            'lunas'                => Fine::where('status', 'lunas')->count(),
        ];

        $totalUnpaid = Fine::where('status', 'belum_dibayar')->sum('amount');

        return view('admin.fines.index', compact('fines', 'status', 'counts', 'totalUnpaid'));
    }

    public function verifyPayment(Request $request, Fine $fine)
    {
        // Panggil endpoint API Admin secara internal
        $response = $this->callApi('POST', "/api/admin/fines/{$fine->id}/verify", [
            'action' => $request->input('action')
        ]);

        if (isset($response['message'])) {
            return back()->with('success', $response['message']);
        }

        return back()->with('error', $response['message'] ?? 'Gagal memverifikasi denda.');
    }
}
