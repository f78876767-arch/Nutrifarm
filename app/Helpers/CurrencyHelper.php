<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Format number to Indonesian Rupiah
     */
    public static function formatRupiah($amount, $includeCurrency = true)
    {
        if ($includeCurrency) {
            return 'Rp ' . number_format($amount, 0, ',', '.');
        }
        
        return number_format($amount, 0, ',', '.');
    }
    
    /**
     * Parse Rupiah string to number
     */
    public static function parseRupiah($rupiahString)
    {
        // Remove Rp, spaces, and dots, replace comma with dot for decimal
        $cleaned = preg_replace('/[Rp\s\.]/', '', $rupiahString);
        $cleaned = str_replace(',', '.', $cleaned);
        
        return (float) $cleaned;
    }
}
