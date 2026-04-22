<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Branch;
use App\Models\PosTerminal;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    /**
     * Display a listing of inventory movements
     */
    public function index(Request $request)
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

        // Get filter parameters
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'desc');
        $filterBranchId = $request->query('branch_id', null);
        $filterType = $request->query('type', null);
        $search = $request->query('search', '');
        $dateFrom = $request->query('date_from', null);
        $dateTo = $request->query('date_to', null);

        // Whitelist allowed columns
        $allowedColumns = ['id', 'created_at', 'product_id', 'quantity_change', 'type'];
        if (!in_array($sortBy, $allowedColumns)) {
            $sortBy = 'created_at';
        }

        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        // Determine which branch(es) to display
        $displayBranchId = null;
        if ($isAdmin && $filterBranchId) {
            $displayBranchId = (int) $filterBranchId;
        } elseif ($userDefaultBranchId) {
            $displayBranchId = $userDefaultBranchId;
        }

        // Build query
        $query = InventoryMovement::with(['product', 'branch', 'user']);

        // Filter by branch
        if ($displayBranchId) {
            $query->where('branch_id', $displayBranchId);
        }

        // Filter by type
        if ($filterType && in_array($filterType, ['in', 'out', 'adjustment'])) {
            $query->where('type', $filterType);
        }

        // Search by product name or ID
        if (!empty($search)) {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        // Apply sorting
        if ($sortBy === 'product_id') {
            $query->join('products', 'inventory_movements.product_id', '=', 'products.id')
                  ->select('inventory_movements.*')
                  ->orderBy('products.name', $sortDir);
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        $movements = $query->paginate(15);

        return view('modules.inventory.stock-movements', [
            'movements' => $movements,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'filterBranchId' => $filterBranchId,
            'filterType' => $filterType,
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'isAdmin' => $isAdmin,
            'branches' => $branches,
        ]);
    }
}

