<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['purchase_id','date_issued','total_amount','date_due'];
    protected $casts = [
        'total_amount' => 'float',
        'date_issued' => 'datetime',
        'date_due' => 'datetime',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }
}
