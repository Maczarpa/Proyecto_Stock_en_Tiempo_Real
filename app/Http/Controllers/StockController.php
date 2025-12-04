<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Events\StockUpdated;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return view('stock', compact('products'));
    }

    public function updateStock(Request $request, Product $product)
    {
        $request->validate([
            'stock' => 'required|integer|min:0'
        ]);

        $product->update([
            'stock' => $request->stock
        ]);

        // Emitir evento de actualización
        broadcast(new StockUpdated($product));

        return response()->json([
            'success' => true,
            'message' => 'Stock actualizado correctamente'
        ]);
    }
}