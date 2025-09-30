<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // When order status changes to completed, update product sales
        if ($order->isDirty('status') && $order->status === 'completed') {
            if (method_exists($order, 'orderItems') && $order->orderItems) {
                foreach ($order->orderItems as $item) {
                    $product = $item->product;
                    if ($product) {
                        $product->incrementSales($item->quantity);
                    }
                }
            }
        }
    }
}
