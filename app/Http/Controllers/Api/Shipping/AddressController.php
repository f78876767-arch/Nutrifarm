<?php

namespace App\Http\Controllers\Api\Shipping;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    // Save/update user's structured address (requires auth)
    public function upsert(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:30',
            'province_id' => 'required|integer',
            'province_name' => 'required|string',
            'city_id' => 'required|integer',
            'city_name' => 'required|string',
            'postal_code' => 'nullable|string|max:10',
            'subdistrict_id' => 'nullable|integer',
            'subdistrict_name' => 'nullable|string',
        ]);
        $user->update($data);
        return response()->json($user->only([
            'address','phone','province_id','province_name','city_id','city_name','postal_code','subdistrict_id','subdistrict_name'
        ]));
    }

    // Get current user's address profile (requires auth)
    public function me()
    {
        $user = Auth::user();
        return response()->json($user->only([
            'address','phone','province_id','province_name','city_id','city_name','postal_code','subdistrict_id','subdistrict_name'
        ]));
    }
}
