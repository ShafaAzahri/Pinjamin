<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = ['user_id', 'status', 'loan_duration_hours', 'approved_by', 'approved_at', 'returned_at'];

    protected $casts = [
        'approved_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function loanItems()
    {
        return $this->hasMany(LoanItem::class);
    }

    public function fines()
    {
        return $this->hasMany(Fine::class);
    }
}
