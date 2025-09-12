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
        // Validate required fields per J&T docs
        $payload = $request->validate([
            'sendSiteCode' => 'required|string',
            'destAreaCode' => 'required|string',
            'weight' => 'required|numeric',
        ]);
        $resp = $svc->tariffInquiry($payload);
        // If upstream failed, surface a 502 so HTTP logs mirror reality
        if (is_array($resp) && isset($resp['is_success']) && $resp['is_success'] === 'false') {
            return response()->json($resp, 502);
        }
        return response()->json($resp);
    }

    public function track(Request $request, JntService $svc)
    {
        $payload = $request->all();
        $resp = $svc->track($payload);
        return response()->json($resp);
    }
}
