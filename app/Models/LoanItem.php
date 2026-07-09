<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanItem extends Model
{
    protected $fillable = ['loan_id', 'item_unit_id', 'return_proof_photo', 'return_condition'];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function unit()
    {
        return $this->belongsTo(ItemUnit::class, 'item_unit_id');
    }
}
