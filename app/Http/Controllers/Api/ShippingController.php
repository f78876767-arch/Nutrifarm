<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Shipping\JntService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShippingController extends Controller
{
    protected JntService $jntService;

    public function __construct(JntService $jntService)
    {
        $this->jntService = $jntService;
    }

    public function jntTariff(Request $request): JsonResponse
    {
        $request->validate([
            'sendSiteCode' => 'required|string',
            'destAreaCode' => 'required|string',
            'weight' => 'required|numeric|min:0.1',
        ]);

        $result = $this->jntService->tariffInquiry([
            'sendSiteCode' => $request->sendSiteCode,
            'destAreaCode' => $request->destAreaCode,
            'weight' => $request->weight,
        ]);

        return response()->json($result);
    }

    public function jntCreateOrder(Request $request): JsonResponse
    {
        $request->validate([
            'order_no' => 'required|string',
            'shipper' => 'required|array',
            'shipper.name' => 'required|string',
            'shipper.phone' => 'required|string',
            'shipper.address' => 'required|string',
            'shipper.city' => 'required|string',
            'shipper.area' => 'required|string',
            'receiver' => 'required|array',
            'receiver.name' => 'required|string',
            'receiver.phone' => 'required|string',
            'receiver.address' => 'required|string',
            'receiver.city' => 'required|string',
            'receiver.area' => 'required|string',
            'goods' => 'required|array',
            'goods.*.name' => 'required|string',
            'goods.*.quantity' => 'required|integer|min:1',
            'goods.*.weight' => 'required|numeric|min:0.1',
            'goods.*.value' => 'required|numeric|min:0',
            'service_type' => 'string',
            'cod' => 'numeric',
            'insurance' => 'numeric',
        ]);

        $result = $this->jntService->createOrder($request->all());

        return response()->json($result);
    }

    public function jntTrack(Request $request): JsonResponse
    {
        $request->validate([
            'awb' => 'required|string',
        ]);

        $result = $this->jntService->track([
            'awb' => $request->awb,
        ]);

        return response()->json($result);
    }
}
