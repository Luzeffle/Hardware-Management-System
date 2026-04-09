<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchInventory extends Model
{
    protected $table = 'branch_inventory';
    protected $fillable = ['branch_id','product_id','quantity','capital'];
    protected $casts = [
        'quantity' => 'float',
        'capital' => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
