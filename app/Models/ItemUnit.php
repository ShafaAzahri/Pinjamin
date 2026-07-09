<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemUnit extends Model
{
    protected $fillable = ['item_id', 'serial_number', 'condition', 'status'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
