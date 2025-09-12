<?php

namespace App\Http\Controllers\Api\Shipping;

use App\Http\Controllers\Controller;
use App\Services\Shipping\JNTTariffService;
use Illuminate\Http\Request;

class JNTTariffController extends Controller
{
    public function __construct(private readonly JNTTariffService $service)
    {
    }

    public function check(Request $request)
    {
        $data = $request->validate([
            'sendSiteCode' => ['required','string'],
            'destAreaCode' => ['required','string'],
            'weight' => ['required','numeric'], // kilograms per J&T examples
        ]);

        $result = $this->service->checkTariff(
            $data['sendSiteCode'],
            $data['destAreaCode'],
            (float) $data['weight']
        );

        return response()->json($result);
    }
}
