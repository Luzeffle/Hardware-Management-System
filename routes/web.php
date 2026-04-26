<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('modules.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

# Routes for logged in users
Route::middleware('auth')->group(function () {
    Route::get('/pos', function () {
        return view('modules.pos.new-sale');
    })->middleware('permission:pos.access')->name('pos');

    Route::get('/pos/transactions', [\App\Http\Controllers\Pos\TransactionController::class, 'index'])
        ->middleware('permission:sales.view-history')->name('pos.transactions');


    Route::get('/purchasing/new-invoice', function () {
        return view('modules.purchasing.new-invoice');
    })->middleware('permission:purchases.create')->name('purchasing.new-invoice');

    Route::get('/purchasing/invoice-history', function () {
        return view('modules.purchasing.invoice-history');
    })->middleware('permission:purchases.view-history')->name('purchasing.invoice-history');

    Route::get('/inventory/overview', [\App\Http\Controllers\Inventory\OverviewController::class, 'index'])
        ->middleware('permission:inventory.view-overview')->name('inventory.overview');

    Route::get('/inventory/manual-stock-in', [\App\Http\Controllers\Inventory\StockInController::class, 'create'])
        ->middleware('permission:inventory.update')->name('inventory.manual-stock-in');

    Route::get('/inventory/stock-out', [\App\Http\Controllers\Inventory\StockOutController::class, 'create'])
        ->middleware('permission:inventory.update')->name('inventory.stock-out');

    Route::get('/inventory/stock-movements', [\App\Http\Controllers\Inventory\StockMovementController::class, 'index'])
        ->middleware('permission:inventory.view-movements')->name('inventory.stock-movements');

    Route::get('/inventory/archives', function () {
        return view('modules.inventory.archives');
    })->middleware('permission:inventory.archive')->name('inventory.archives');

    Route::get('/audit-logs/user-activity', function () {
        return view('modules.audit-logs.user-activity');
    })->middleware('permission:audit.user-activity.view')->name('audit-logs.user-activity');

    Route::get('/audit-logs/system-logs', function () {
        return view('modules.audit-logs.system-logs');
    })->middleware('permission:audit.system-logs.view')->name('audit-logs.system-logs');

    Route::get('/audit-logs/archives', function () {
        return view('modules.audit-logs.archives');
    })->middleware('permission:audit.system-logs.view')->name('audit-logs.archives');

    // Supplier routes
    Route::resource('suppliers', \App\Http\Controllers\SupplierController::class)
        ->middleware('permission:suppliers.view');

    // POS API endpoints
    Route::prefix('pos/api')->group(function () {
        Route::get('products/search', [\App\Http\Controllers\Pos\ProductController::class, 'search'])->name('pos.api.products.search');
        Route::get('products/browse', [\App\Http\Controllers\Pos\ProductController::class, 'browse'])->name('pos.api.products.browse');

        Route::get('cart', [\App\Http\Controllers\Pos\PosController::class, 'getCart'])->name('pos.api.cart.get');
        Route::post('cart/add', [\App\Http\Controllers\Pos\PosController::class, 'addItem'])->name('pos.api.cart.add');
        Route::post('cart/update', [\App\Http\Controllers\Pos\PosController::class, 'updateItem'])->name('pos.api.cart.update');
        Route::post('cart/remove', [\App\Http\Controllers\Pos\PosController::class, 'removeItem'])->name('pos.api.cart.remove');

        Route::get('checkout/prepare', [\App\Http\Controllers\Pos\CheckoutController::class, 'prepare'])->name('pos.api.checkout.prepare');
        Route::post('checkout/finalize', [\App\Http\Controllers\Pos\CheckoutController::class, 'finalize'])->name('pos.api.checkout.finalize');
    });

    // Inventory API endpoints
    Route::prefix('inventory/api')->group(function () {
        Route::get('products/search', [\App\Http\Controllers\Inventory\StockInController::class, 'searchProducts'])->name('inventory.api.products.search');
        Route::post('stock-in/store', [\App\Http\Controllers\Inventory\StockInController::class, 'store'])->name('inventory.api.stock-in.store');
        Route::post('stock-out/store', [\App\Http\Controllers\Inventory\StockOutController::class, 'store'])->name('inventory.api.stock-out.store');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
