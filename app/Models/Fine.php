<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    protected $fillable = ['loan_id', 'amount', 'type', 'status', 'payment_proof_photo', 'verified_by', 'verified_at', 'snap_token'];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
