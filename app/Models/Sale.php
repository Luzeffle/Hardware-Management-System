<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['date','user_id','total_amount','branch_id'];
    protected $casts = [
        'total_amount' => 'float',
        'date' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
