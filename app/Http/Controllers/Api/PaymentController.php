<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Set Midtrans configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * POST /api/student/fines/{fine}/pay
     */
    public function pay(Request $request, Fine $fine)
    {
        if ($fine->status === 'lunas') {
            return response()->json(['message' => 'Denda ini sudah lunas.'], 422);
        }

        if ($fine->loan->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Jika snap_token sudah ada, return token tersebut (menghindari duplikasi transaksi jika belum expired)
        if ($fine->snap_token) {
            return response()->json([
                'snap_token' => $fine->snap_token
            ]);
        }

        $user = auth()->user();

        // Buat payload untuk Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => 'FINE-' . $fine->id . '-' . time(),
                'gross_amount' => $fine->amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'item_details' => [
                [
                    'id'       => 'FINE-' . $fine->id,
                    'price'    => $fine->amount,
                    'quantity' => 1,
                    'name'     => 'Denda ' . ucfirst(str_replace('_', ' ', $fine->type)),
                ]
            ]
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            // Simpan snap_token ke database
            $fine->update([
                'snap_token' => $snapToken
            ]);

            return response()->json([
                'snap_token' => $snapToken
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menghubungi payment gateway.'], 500);
        }
    }

    /**
     * POST /api/payments/webhook
     * Webhook Endpoint (No CSRF & No Auth)
     */
    public function webhook(Request $request)
    {
        try {
            $notif = new Notification();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Midtrans Webhook Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request_body' => request()->all()
            ]);

            // Jika error adalah karena transaksi tidak ditemukan di Midtrans (biasanya saat tes URL di dashboard)
            if (str_contains($e->getMessage(), "Transaction doesn't exist")) {
                return response()->json(['message' => 'Webhook test connection successful'], 200);
            }

            return response()->json(['message' => 'Invalid signature: ' . $e->getMessage()], 403);
        }

        $transactionStatus = $notif->transaction_status;
        $orderId = $notif->order_id;
        $fraudStatus = $notif->fraud_status;

        // Ambil ID Denda dari Order ID (format: FINE-{id}-{time})
        $parts = explode('-', $orderId);
        if (count($parts) < 2 || $parts[0] !== 'FINE') {
            return response()->json(['message' => 'Invalid order ID format'], 400);
        }

        $fineId = $parts[1];
        $fine = Fine::find($fineId);

        if (!$fine) {
            return response()->json(['message' => 'Fine not found'], 404);
        }

        $isPaid = false;

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                // Menunggu verifikasi
            } else if ($fraudStatus == 'accept') {
                $fine->update(['status' => 'lunas']);
                $isPaid = true;
            }
        } else if ($transactionStatus == 'settlement') {
            $fine->update(['status' => 'lunas']);
            $isPaid = true;
        } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            // Bisa mengosongkan snap_token agar user bisa bayar ulang
            $fine->update(['snap_token' => null]);
        } else if ($transactionStatus == 'pending') {
            // Menunggu pembayaran
        }

        if ($isPaid) {
            $user = $fine->loan->user;
            if ($user && $user->phone) {
                $formattedAmount = number_format($fine->amount, 0, ',', '.');
                $message = "Halo *{$user->name}*,\n\nPembayaran denda Anda untuk peminjaman ID *L" . str_pad($fine->loan_id, 3, '0', STR_PAD_LEFT) . "* sebesar *Rp {$formattedAmount}* telah *BERHASIL TERVERIFIKASI* (Lunas).\n\nTerima kasih atas kerja samanya.";
                
                \App\Services\WhatsAppService::send($user->phone, $message);
            }
        }

        return response()->json(['message' => 'Notification processed']);
    }
}
