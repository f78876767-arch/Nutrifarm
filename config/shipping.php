<?php

return [
    // City ID (numeric) of the store's origin (RajaOngkir city_id)
    'origin_city_id' => (int) env('SHIPPING_ORIGIN_CITY_ID', 0),
    'origin_type' => env('SHIPPING_ORIGIN_TYPE', 'city'), // city|subdistrict (pro)

    // Default item weight in grams if product weights are not tracked yet
    'default_item_weight_g' => (int) env('SHIPPING_DEFAULT_ITEM_WEIGHT_G', 250),
];
