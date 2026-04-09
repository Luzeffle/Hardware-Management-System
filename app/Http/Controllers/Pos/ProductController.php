<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\BranchInventory;

class ProductController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->query('q');
        $branchId = $request->query('branch_id');

        $productsQuery = Product::query()
            ->search($q)
            ->where('status', 'active');

        $products = $productsQuery->limit(20)->get(['id','name','unit','capital']);

        // attach branch inventory qty when branch_id is provided
        if ($branchId && $products->isNotEmpty()) {
            $prodIds = $products->pluck('id')->all();
            $inventories = BranchInventory::where('branch_id', $branchId)
                ->whereIn('product_id', $prodIds)
                ->get()
                ->keyBy('product_id');

            $products = $products->map(function ($p) use ($inventories) {
                $p->available_quantity = optional($inventories->get($p->id))->quantity ?? 0;
                return $p;
            });
        }

        return response()->json($products);
    }

    public function browse(Request $request)
    {
        $q = $request->query('q');
        $perPage = (int) $request->query('per_page', 25);
        $products = Product::query()
            ->search($q)
            ->where('status', 'active')
            ->paginate($perPage, ['id','name','unit','capital']);

        return response()->json($products);
    }
}
