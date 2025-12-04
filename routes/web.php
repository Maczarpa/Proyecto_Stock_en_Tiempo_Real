<?php

use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StockController::class, 'index'])->name('stock.index');
Route::post('/products/{product}/stock', [StockController::class, 'updateStock'])->name('stock.update');
Route::post('/products', [StockController::class, 'store'])->name('stock.store');