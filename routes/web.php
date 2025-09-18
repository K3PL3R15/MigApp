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

// Rutas protegidas por autenticación
Route::middleware(['auth'])->group(function () {
    
    // === GESTIÓN DE SUCURSALES ===
    Route::prefix('branches')->name('branches.')->group(function () {
        Route::get('/', [BranchController::class, 'index'])->name('index');
        Route::get('/create', [BranchController::class, 'create'])->name('create');
        Route::post('/', [BranchController::class, 'store'])->name('store');
        Route::get('/{branch}/edit', [BranchController::class, 'edit'])->name('edit');
        Route::put('/{branch}', [BranchController::class, 'update'])->name('update');
        Route::delete('/{branch}', [BranchController::class, 'destroy'])->name('destroy');
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
        Route::post('/{transfer}/approve', [RequestTransferController::class, 'approve'])->name('approve');
        Route::post('/{transfer}/reject', [RequestTransferController::class, 'reject'])->name('reject');
        Route::post('/{transfer}/complete', [RequestTransferController::class, 'complete'])->name('complete');
        Route::delete('/{transfer}', [RequestTransferController::class, 'destroy'])->name('destroy');
    });
    
    // === GESTIÓN DE USUARIOS ===
    Route::prefix('users')->name('users.')->group(function () {
        // Rutas básicas de usuarios (si existe UserController)
        Route::get('/', function () {
            return redirect()->route('dashboard')->with('info', 'Módulo de usuarios en desarrollo');
        })->name('index');
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
