<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:check-overdue-loans')]
#[Description('Mengecek peminjaman aktif yang sudah melewati batas waktu pengembalian, mengubah statusnya menjadi terlambat, dan mengirim notifikasi WhatsApp.')]
class CheckOverdueLoans extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
        $this->info("[$now] Memulai pengecekan peminjaman terlambat...");

        // Cari peminjaman berstatus aktif
        $activeLoans = \App\Models\Loan::where('status', 'aktif')->with(['user', 'loanItems.unit.item'])->get();
        $updatedCount = 0;

        foreach ($activeLoans as $loan) {
            if (!$loan->approved_at) {
                continue;
            }

            $deadline = \Carbon\Carbon::parse($loan->approved_at);
            if ($loan->loan_duration_type === 'days') {
                $deadline->addDays($loan->loan_duration);
            } else {
                $deadline->addHours($loan->loan_duration);
            }

            if ($now->greaterThan($deadline)) {
                $loan->update(['status' => 'terlambat']);
                $updatedCount++;

                $user = $loan->user;
                if ($user && $user->phone) {
                    $itemNames = $loan->loanItems->map(function ($li) {
                        return ($li->unit->item->name ?? 'Barang') . ' (' . ($li->unit->serial_number ?? '-') . ')';
                    })->implode(', ');

                    $fineAmount = (int) (\App\Models\Setting::where('key', 'fine_amount')->first()?->value ?? 5000);
                    $fineType = \App\Models\Setting::where('key', 'fine_type')->first()?->value ?? 'per_hour';
                    $formattedFine = number_format($fineAmount, 0, ',', '.');
                    $fineLabel = $fineType === 'per_day' ? 'hari' : 'jam';

                    $message = "Halo *{$user->name}*,\n\nPeminjaman alat Anda dengan ID *L" . str_pad($loan->id, 3, '0', STR_PAD_LEFT) . "* telah *MELEWATI BATAS WAKTU* pengembalian.\n\n" .
                               "Barang: {$itemNames}\n" .
                               "Batas Waktu: " . $deadline->format('d M Y H:i') . " WIB\n\n" .
                               "Saat ini Anda dikenakan denda keterlambatan berjalan sebesar *Rp {$formattedFine}/{$fineLabel}*. Harap segera mengembalikan alat tersebut ke Laboratorium dan memverifikasi pengembalian di aplikasi Pinjamin.\n\nTerima kasih.";

                    \App\Services\WhatsAppService::send($user->phone, $message);
                }

                $this->line("Peminjaman ID L{$loan->id} milik {$user->name} diubah menjadi terlambat.");
            }
        }

        $this->info("Pengecekan selesai. {$updatedCount} peminjaman diperbarui ke status terlambat.");
    }
}
