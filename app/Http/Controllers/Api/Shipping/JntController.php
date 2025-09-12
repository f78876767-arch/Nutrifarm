<?php

namespace App\Http\Controllers\Api\Shipping;

use App\Http\Controllers\Controller;
use App\Services\Shipping\JntService;
use Illuminate\Http\Request;

class JntController extends Controller
{
    public function createOrder(Request $request, JntService $svc)
    {
        $data = $request->validate([
            'order_no' => 'required|string',
            'shipper' => 'required|array',
            'receiver' => 'required|array',
            'goods' => 'required|array',
            // other J&T required fields should be validated as needed
        ]);
        $resp = $svc->createOrder($data);
        return response()->json($resp);
    }

    public function cancelOrder(Request $request, JntService $svc)
    {
        $data = $request->validate([
            'order_no' => 'required|string',
            // add additional identifiers required by J&T
        ]);
        $resp = $svc->cancelOrder($data);
        return response()->json($resp);
    }

    public function tariff(Request $request, JntService $svc)
    {
        // J&T sample uses form params; adjust keys per docs
        $payload = $request->all();
        $resp = $svc->tariffInquiry($payload);
        return response()->json($resp);
    }

    public function track(Request $request, JntService $svc)
    {
        $payload = $request->all();
        $resp = $svc->track($payload);
        return response()->json($resp);
    }
}
