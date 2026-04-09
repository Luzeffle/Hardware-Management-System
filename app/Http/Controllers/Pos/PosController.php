<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PosController extends Controller
{
    protected $sessionKey = 'pos_cart';

    public function getCart(Request $request)
    {
        $cart = $request->session()->get($this->sessionKey, []);
        return response()->json($cart);
    }

    public function addItem(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'nullable|numeric|min:0.01',
        ]);

        $quantity = $data['quantity'] ?? 1;
        $cart = $request->session()->get($this->sessionKey, []);

        // merge if exists
        $found = false;
        foreach ($cart as &$item) {
            if ($item['product_id'] == $data['product_id']) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        unset($item);

        if (! $found) {
            $cart[] = ['product_id' => $data['product_id'], 'quantity' => $quantity];
        }

        $request->session()->put($this->sessionKey, $cart);
        return response()->json($cart);
    }

    public function updateItem(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|numeric|min:0',
        ]);

        $cart = $request->session()->get($this->sessionKey, []);
        foreach ($cart as $k => $item) {
            if ($item['product_id'] == $data['product_id']) {
                if ($data['quantity'] <= 0) {
                    unset($cart[$k]);
                } else {
                    $cart[$k]['quantity'] = $data['quantity'];
                }
                break;
            }
        }

        $cart = array_values($cart);
        $request->session()->put($this->sessionKey, $cart);
        return response()->json($cart);
    }

    public function removeItem(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer',
        ]);

        $cart = $request->session()->get($this->sessionKey, []);
        foreach ($cart as $k => $item) {
            if ($item['product_id'] == $data['product_id']) {
                unset($cart[$k]);
                break;
            }
        }

        $cart = array_values($cart);
        $request->session()->put($this->sessionKey, $cart);
        return response()->json($cart);
    }
}
