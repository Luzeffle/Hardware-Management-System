<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $table = 'inventory_movements';
    protected $fillable = ['product_id', 'branch_id', 'user_id', 'type', 'quantity_change', 'reference_type', 'reference_id', 'created_at'];
    protected $casts = [
        'quantity_change' => 'float',
        'created_at' => 'datetime',
    ];
    public $timestamps = false;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

