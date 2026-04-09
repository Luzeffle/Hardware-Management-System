<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\BranchInventory;

class CheckoutController extends Controller
{
    public function prepare(Request $request)
    {
        $cart = $request->session()->get('pos_cart', []);
        $productIds = array_column($cart, 'product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $items = [];
        $total = 0;
        foreach ($cart as $c) {
            $p = $products[$c['product_id']];
            $qty = $c['quantity'];
            $unitPrice = $p->capital; // defaulting to capital if no price column
            $subtotal = $unitPrice * $qty;
            $items[] = [
                'product_id' => $p->id,
                'product_name' => $p->name,
                'unit' => $p->unit,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'cost' => $p->capital,
                'subtotal' => $subtotal,
            ];
            $total += $subtotal;
        }

        return response()->json([
            'items' => $items,
            'total' => $total,
            'payment_methods' => ['cash','cheque'],
        ]);
    }

    public function finalize(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|integer',
            'payment_method' => 'required|string|in:cash,cheque',
            'payment_details' => 'nullable|array',
        ]);

        $cart = $request->session()->get('pos_cart', []);
        if (empty($cart)) {
            return response()->json(['message' => 'Cart is empty'], 422);
        }

        $productIds = array_column($cart, 'product_id');

        return DB::transaction(function () use ($request, $cart, $data, $productIds) {
            $inventories = BranchInventory::where('branch_id', $data['branch_id'])
                ->whereIn('product_id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            $total = 0;
            foreach ($cart as $c) {
                $pid = $c['product_id'];
                $qty = $c['quantity'];
                $inv = $inventories[$pid] ?? null;
                if (! $inv || $inv->quantity < $qty) {
                    abort(422, 'Insufficient stock for product '.$pid);
                }
                $unitPrice = $products[$pid]->capital; // adjust if you have price
                $subtotal = $unitPrice * $qty;
                $total += $subtotal;
                $inv->decrement('quantity', $qty);
            }

            $sale = Sale::create([
                'date' => now(),
                'user_id' => $request->user()->id,
                'total_amount' => $total,
                'branch_id' => $data['branch_id'],
            ]);

            foreach ($cart as $c) {
                $p = $products[$c['product_id']];
                $unitPrice = $p->capital;
                $sale->items()->create([
                    'product_id' => $p->id,
                    'product_name' => $p->name,
                    'unit' => $p->unit,
                    'quantity' => $c['quantity'],
                    'unit_price' => $unitPrice,
                    'cost' => $p->capital,
                    'subtotal' => $unitPrice * $c['quantity'],
                ]);
            }

            // clear cart
            $request->session()->forget('pos_cart');

            return response()->json(['sale_id' => $sale->id, 'total' => $total]);
        });
    }
}
