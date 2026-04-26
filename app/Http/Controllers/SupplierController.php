<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers with sorting and search
     */
    public function index(Request $request)
    {
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'desc');
        $search = $request->query('search', '');
        $status = $request->query('status', '');

        // Whitelist allowed columns for sorting
        $allowedColumns = ['id', 'supplier_name', 'contact_person', 'contact_number', 'status', 'created_at'];
        if (!in_array($sortBy, $allowedColumns)) {
            $sortBy = 'created_at';
        }

        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        // Build query
        $query = Supplier::query();

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('supplier_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('contact_email', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }

        // Apply sorting and pagination
        $suppliers = $query->orderBy($sortBy, $sortDir)->paginate(15);

        return view('modules.suppliers.suppliers', [
            'suppliers' => $suppliers,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'search' => $search,
            'status' => $status,
        ]);
    }

    /**
     * Show the form for creating a new supplier
     */
    public function create()
    {
        return view('modules.suppliers.create');
    }

    /**
     * Store a newly created supplier in the database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255|unique:suppliers,supplier_name',
            'contact_person' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'contact_number' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            Supplier::create($validated);

            return redirect()->route('suppliers.index')
                ->with('success', 'Supplier created successfully');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create supplier: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified supplier with related data
     */
    public function show(Supplier $supplier)
    {
        $purchases = $supplier->purchases()->with('branch')->paginate(10);
        $invoices = $supplier->invoices()->latest()->paginate(10);
        $totalPurchases = $supplier->purchases()->count();
        $totalAmount = DB::table('purchases')
            ->join('purchase_details', 'purchases.id', '=', 'purchase_details.purchase_id')
            ->where('purchases.supplier_id', $supplier->id)
            ->sum('purchase_details.subtotal');

        return view('modules.suppliers.show', [
            'supplier' => $supplier,
            'purchases' => $purchases,
            'invoices' => $invoices,
            'totalPurchases' => $totalPurchases,
            'totalAmount' => $totalAmount,
        ]);
    }

    /**
     * Show the form for editing the specified supplier
     */
    public function edit(Supplier $supplier)
    {
        return view('modules.suppliers.edit', [
            'supplier' => $supplier,
        ]);
    }

    /**
     * Update the specified supplier in the database
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255|unique:suppliers,supplier_name,' . $supplier->id,
            'contact_person' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'contact_number' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            $supplier->update($validated);

            return redirect()->route('suppliers.show', $supplier)
                ->with('success', 'Supplier updated successfully');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update supplier: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified supplier from the database
     */
    public function destroy(Supplier $supplier)
    {
        try {
            // Check if supplier has associated purchases
            if ($supplier->purchases()->exists()) {
                return back()->with('error', 'Cannot delete supplier with existing purchases. Deactivate instead.');
            }

            $supplier->delete();

            return redirect()->route('suppliers.index')
                ->with('success', 'Supplier deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete supplier: ' . $e->getMessage());
        }
    }
}

