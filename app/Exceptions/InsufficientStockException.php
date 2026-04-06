<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    protected $product;

    public function __construct($product)
    {
        $this->product = $product;
        parent::__construct("Insufficient stock for product: {$product->name}. Current stock: {$product->current_stock}");
    }

    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Insufficient Stock',
                'message' => $this->getMessage(),
                'product_id' => $this->product->id,
            ], 422);
        }

        return back()->with('error', $this->getMessage());
    }
}
