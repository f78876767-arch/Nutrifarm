<?php
namespace App\Http\Controllers\Api\Shipping;

use App\Http\Controllers\Controller;
use App\Services\Shipping\JntService;
use Illuminate\Http\Request;

class JntController extends Controller
{
    public function createOrder(Request $request, JntService $svc)
    {
        // Validate required fields for J&T order creation
        $data = $request->validate([
            'order_no' => 'required|string|max:50',
            'service_type' => 'nullable|string|in:EZ,REG',
            'cod' => 'nullable|numeric|min:0',
            'insurance' => 'nullable|numeric|min:0',
            'remark' => 'nullable|string|max:255',
            
            // Shipper information
            'shipper.name' => 'required|string|max:100',
            'shipper.phone' => 'required|string|max:20',
            'shipper.area' => 'required|string|max:100',
            'shipper.address' => 'required|string|max:255',
            'shipper.postcode' => 'nullable|string|max:10',
            
            // Receiver information  
            'receiver.name' => 'required|string|max:100',
            'receiver.phone' => 'required|string|max:20',
            'receiver.area' => 'required|string|max:100',
            'receiver.address' => 'required|string|max:255',
            'receiver.postcode' => 'nullable|string|max:10',
            
            // Goods information
            'goods.*.name' => 'required|string|max:100',
            'goods.*.qty' => 'required|integer|min:1',
            'goods.*.weight' => 'required|numeric|min:0.1',
            'goods.*.value' => 'nullable|numeric|min:0',
            'goods.*.length' => 'nullable|numeric|min:0',
            'goods.*.width' => 'nullable|numeric|min:0', 
            'goods.*.height' => 'nullable|numeric|min:0',
        ]);

        $resp = $svc->createOrder($data);
        
        // If upstream failed, surface a 502 so HTTP logs mirror reality
        if (is_array($resp) && isset($resp['is_success']) && $resp['is_success'] === 'false') {
            return response()->json($resp, 502);
        }
        
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
        $data = $request->validate([
            'awb' => 'required|string',
        ]);
        
        $result = $svc->trackShipment($data['awb']);
        return response()->json($result);
    }
}
