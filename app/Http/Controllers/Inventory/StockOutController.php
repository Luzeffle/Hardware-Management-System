<?php
namespace App\Http\Controllers\Inventory;
use App\Http\Controllers\Controller;
use App\Models\BranchInventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Branch;
use App\Models\PosTerminal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class StockOutController extends Controller
{
    /**
     * Show the manual stock-out form
     */
    public function create(Request $request)
    {
        // Get user's terminal branch (if assigned)
        $terminalId = $request->session()->get('pos_terminal_id');
        $userDefaultBranchId = null;
        if ($terminalId) {
            $terminal = PosTerminal::find($terminalId);
            $userDefaultBranchId = $terminal?->branch_id;
        }
        $isAdmin = auth()->user()->hasRole('admin');
        $branches = $isAdmin ? Branch::all() : collect();
        return view('modules.inventory.stock-out', [
            'isAdmin' => $isAdmin,
            'branches' => $branches,
            'userDefaultBranchId' => $userDefaultBranchId,
        ]);
    }
    /**
     * Search products for stock-out (API endpoint)
     */
    public function searchProducts(Request $request)
    {
        $q = $request->query('q');
        $limit = min(max((int) $request->query('limit', 20), 1), 50);
        $productsQuery = Product::query()
            ->search($q)
            ->where('status', 'active');
        $products = $productsQuery
            ->limit($limit)
            ->get(['id', 'name', 'unit', 'capital']);
        return response()->json($products);
    }
    /**
     * Store stock-out transaction
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|integer|exists:branches,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'reference_type' => 'nullable|in:sale,transfer,adjustment,other',
            'reference_id' => 'nullable|integer',
            'notes' => 'nullable|string|max:500',
        ]);
        // Check user permission for branch
        $isAdmin = auth()->user()->hasRole('admin');
        if (!$isAdmin) {
            // Non-admin users can only stock-out from their assigned terminal's branch
            $terminalId = $request->session()->get('pos_terminal_id');
            $terminal = PosTerminal::find($terminalId);
            if (!$terminal || $terminal->branch_id != $data['branch_id']) {
                return response()->json(['message' => 'Unauthorized branch access'], 403);
            }
        }
        try {
            return DB::transaction(function () use ($data, $request) {
                $movements = [];
                $totalItems = 0;
                foreach ($data['items'] as $item) {
                    $productId = $item['product_id'];
                    $quantity = (float) $item['quantity'];
                    if ($quantity <= 0) {
                        continue;
                    }
                    // Get branch inventory record
                    $inventory = BranchInventory::where('branch_id', $data['branch_id'])
                        ->where('product_id', $productId)
                        ->first();
                    // Check if product exists in inventory
                    if (!$inventory) {
                        return response()->json([
                            'message' => "Product {$productId} not found in branch inventory"
                        ], 422);
                    }
                    // Check if sufficient stock exists
                    if ($inventory->quantity < $quantity) {
                        return response()->json([
                            'message' => "Insufficient stock for product {$productId}. Available: {$inventory->quantity}"
                        ], 422);
                    }
                    // Decrement quantity
                    $inventory->decrement('quantity', $quantity);
                    // Record movement (negative quantity for stock-out)
                    InventoryMovement::create([
                        'product_id' => $productId,
                        'branch_id' => $data['branch_id'],
                        'user_id' => $request->user()->id,
                        'type' => 'out',
                        'quantity_change' => -$quantity,
                        'reference_type' => $data['reference_type'] ?? 'other',
                        'reference_id' => $data['reference_id'] ?? null,
                    ]);
                    $movements[] = [
                        'product_id' => $productId,
                        'quantity' => $quantity,
                    ];
                    $totalItems += $quantity;
                }
                if (empty($movements)) {
                    return response()->json(['message' => 'No valid items to process'], 422);
                }
                return response()->json([
                    'success' => true,
                    'message' => "Stock-out completed. {$totalItems} units removed.",
                    'movements_count' => count($movements),
                    'total_quantity' => $totalItems,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error processing stock-out: ' . $e->getMessage(),
            ], 500);
        }
    }
}
