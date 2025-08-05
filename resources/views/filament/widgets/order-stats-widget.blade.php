<x-filament::widget>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        <div class="bg-gradient-to-br from-indigo-600 to-indigo-900 text-white rounded-2xl shadow-2xl p-8 flex flex-col items-center transition-transform hover:scale-105">
            <div class="text-base font-semibold mb-1 opacity-90">Total Orders</div>
            <div class="text-5xl font-extrabold tracking-tight drop-shadow">{{ $totalOrders }}</div>
        </div>
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-700 text-white rounded-2xl shadow-2xl p-8 flex flex-col items-center transition-transform hover:scale-105">
            <div class="text-base font-semibold mb-1 opacity-90">Pending</div>
            <div class="text-5xl font-extrabold tracking-tight drop-shadow">{{ $pendingOrders }}</div>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-700 text-white rounded-2xl shadow-2xl p-8 flex flex-col items-center transition-transform hover:scale-105">
            <div class="text-base font-semibold mb-1 opacity-90">Paid</div>
            <div class="text-5xl font-extrabold tracking-tight drop-shadow">{{ $paidOrders }}</div>
        </div>
        <div class="bg-gradient-to-br from-blue-500 to-blue-700 text-white rounded-2xl shadow-2xl p-8 flex flex-col items-center transition-transform hover:scale-105">
            <div class="text-base font-semibold mb-1 opacity-90">Shipped</div>
            <div class="text-5xl font-extrabold tracking-tight drop-shadow">{{ $shippedOrders }}</div>
        </div>
        <div class="bg-gradient-to-br from-red-500 to-red-700 text-white rounded-2xl shadow-2xl p-8 flex flex-col items-center transition-transform hover:scale-105">
            <div class="text-base font-semibold mb-1 opacity-90">Cancelled</div>
            <div class="text-5xl font-extrabold tracking-tight drop-shadow">{{ $cancelledOrders }}</div>
        </div>
    </div>
</x-filament::widget>
