<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fine;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashed !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transactionStatus = $request->transaction_status;
        $orderId = $request->order_id; // format: FINE-{fine_id}-{timestamp}

        // Extract fine ID
        $parts = explode('-', $orderId);
        if (count($parts) < 2 || $parts[0] !== 'FINE') {
            return response()->json(['message' => 'Invalid order ID format'], 400);
        }
        
        $fineId = $parts[1];
        $fine = Fine::find($fineId);

        if (!$fine) {
            return response()->json(['message' => 'Fine not found'], 404);
        }

        Log::info("Midtrans Webhook: Order $orderId status changed to $transactionStatus");

        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            $fine->update([
                'status' => 'lunas'
            ]);
            
            // Optionally, create a notification for the user
            $fine->loan->user->notifications()->create([
                'title' => 'Pembayaran Denda Berhasil',
                'message' => 'Pembayaran denda untuk Peminjaman L' . str_pad($fine->loan_id, 3, '0', STR_PAD_LEFT) . ' telah berhasil dikonfirmasi oleh Midtrans.'
            ]);
        } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $fine->update([
                'status' => 'belum_dibayar',
                'snap_token' => null // Reset snap token so they can try again if expired
            ]);
        }

        return response()->json(['message' => 'Webhook processed successfully']);
    }
}
