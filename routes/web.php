<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\RequestTransferController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - MigApp
|--------------------------------------------------------------------------
*/

// Página de bienvenida
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Dashboard principal
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Binding explícito para inventarios usando la clave primaria correcta
Route::bind('inventory', function ($value) {
    return \App\Models\Inventory::where('id_inventory', $value)->first() ?? abort(404);
});

// Binding explícito para branches usando la clave primaria correcta
Route::bind('branch', function ($value) {
    return \App\Models\Branch::where('id_branch', $value)->first() ?? abort(404);
});

// Binding explícito para request transfers usando la clave primaria correcta
Route::bind('transfer', function ($value) {
    return \App\Models\RequestTransfer::where('id_request', $value)->first() ?? abort(404);
});

// Rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {
    
    // === GESTIÓN DE SUCURSALES ===
    Route::prefix('branches')->name('branches.')->group(function () {
        Route::get('/', [BranchController::class, 'index'])->name('index');
        Route::get('/create', [BranchController::class, 'create'])->name('create');
        Route::post('/', [BranchController::class, 'store'])->name('store');
        Route::get('/explore', [BranchController::class, 'explore'])->name('explore');
        Route::get('/{branch}', [BranchController::class, 'show'])->name('show');
        Route::get('/{branch}/edit', [BranchController::class, 'edit'])->name('edit');
        Route::put('/{branch}', [BranchController::class, 'update'])->name('update');
        Route::delete('/{branch}', [BranchController::class, 'destroy'])->name('destroy');
        
        // Ruta para obtener inventarios de una sucursal (para transferencias)
        Route::get('/{branch}/inventories', [BranchController::class, 'inventories'])->name('inventories');
    });
    
    // === GESTIÓN DE INVENTARIOS ===
    Route::prefix('inventories')->name('inventories.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/create', [InventoryController::class, 'create'])->name('create');
        Route::post('/', [InventoryController::class, 'store'])->name('store');
        Route::get('/{inventory}', [InventoryController::class, 'show'])->name('show');
        Route::get('/{inventory}/edit', [InventoryController::class, 'edit'])->name('edit');
        Route::put('/{inventory}', [InventoryController::class, 'update'])->name('update');
        Route::delete('/{inventory}', [InventoryController::class, 'destroy'])->name('destroy');
        
        // Rutas anidadas para productos dentro de inventarios
        Route::prefix('{inventory}/products')->name('products.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('/{product}', [ProductController::class, 'show'])->name('show');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('/{product}', [ProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
            Route::post('/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('adjust-stock');
        });
        
        // Reportes de panadería
        Route::get('/{inventory}/bakery-report', [ProductController::class, 'bakeryReport'])->name('bakery-report');
    });
    
    // === GESTIÓN DE VENTAS ===
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('/create', [SaleController::class, 'create'])->name('create');
        Route::post('/', [SaleController::class, 'store'])->name('store');
        Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
        Route::get('/{sale}/edit', [SaleController::class, 'edit'])->name('edit');
        Route::put('/{sale}', [SaleController::class, 'update'])->name('update');
        Route::delete('/{sale}', [SaleController::class, 'destroy'])->name('destroy');
        Route::post('/{sale}/email', [SaleController::class, 'emailInvoice'])->name('email-invoice');
    });
    
    // === GESTIÓN DE TRASLADOS ===
    Route::prefix('transfers')->name('transfers.')->group(function () {
        Route::get('/', [RequestTransferController::class, 'index'])->name('index');
        Route::post('/', [RequestTransferController::class, 'store'])->name('store');
        Route::get('/search-products', [RequestTransferController::class, 'searchProducts'])->name('search-products');
        Route::get('/destination-branches', [RequestTransferController::class, 'getDestinationBranches'])->name('destination-branches');
        Route::post('/{transfer}/approve', [RequestTransferController::class, 'approve'])->name('approve');
        Route::post('/{transfer}/reject', [RequestTransferController::class, 'reject'])->name('reject');
        Route::post('/{transfer}/complete', [RequestTransferController::class, 'complete'])->name('complete');
        Route::delete('/{transfer}', [RequestTransferController::class, 'destroy'])->name('destroy');
    });
    
    // === GESTIÓN DE USUARIOS ===
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });
    
    // === PERFIL DE USUARIO ===
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });
});

// Rutas de autenticación
require __DIR__.'/auth.php';
