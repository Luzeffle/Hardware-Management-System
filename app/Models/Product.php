<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    protected $fillable = ['name','capital','unit','status'];
    protected $casts = [
        'capital' => 'float',
    ];

    public function branchInventories()
    {
        return $this->hasMany(BranchInventory::class, 'product_id');
    }

    public function salesItems()
    {
        return $this->hasMany(SaleItem::class, 'product_id');
    }

    public function scopeSearch(Builder $query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        $term = "%{$term}%";
        return $query->where('name', 'like', $term)
                     ->orWhere('unit', 'like', $term);
    }
}
