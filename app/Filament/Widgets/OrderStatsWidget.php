<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Order;

class OrderStatsWidget extends Widget
{
    protected static string $view = 'filament.widgets.order-stats-widget';

    public $totalOrders;
    public $pendingOrders;
    public $paidOrders;
    public $shippedOrders;
    public $cancelledOrders;

    public function mount(): void
    {
        $this->totalOrders = Order::count();
        $this->pendingOrders = Order::where('status', 'pending')->count();
        $this->paidOrders = Order::where('status', 'paid')->count();
        $this->shippedOrders = Order::where('status', 'shipped')->count();
        $this->cancelledOrders = Order::where('status', 'cancelled')->count();
    }
}
