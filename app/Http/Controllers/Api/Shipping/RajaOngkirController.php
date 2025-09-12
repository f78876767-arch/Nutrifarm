<?php

namespace App\Http\Controllers\Api\Shipping;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Shipping\RajaOngkirService;

class RajaOngkirController extends Controller
{
    protected RajaOngkirService $svc;

    public function __construct(RajaOngkirService $svc)
    {
        $this->svc = $svc;
    }

    public function provinces()
    {
        return response()->json($this->svc->provinces());
    }

    public function cities(Request $request)
    {
        $provinceId = $request->query('province');
        return response()->json($this->svc->cities($provinceId ? (int) $provinceId : null));
    }

    public function subdistricts(Request $request)
    {
        $cityId = (int) $request->query('city');
        return response()->json($this->svc->subdistricts($cityId));
    }

    public function cost(Request $request)
    {
        $validated = $request->validate([
            'origin' => 'required',
            'destination' => 'required',
            'weight' => 'required|integer|min:1',
            'courier' => 'required|string',
            'originType' => 'sometimes|nullable|string|in:city,subdistrict',
            'destinationType' => 'sometimes|nullable|string|in:city,subdistrict',
        ]);
        $services = $this->svc->cost($validated);
        return response()->json($services);
    }
}
