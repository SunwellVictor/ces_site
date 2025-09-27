<?php

if (!function_exists('format_yen')) {
    /**
     * Format a price in cents to yen currency format.
     *
     * @param int $cents
     * @return string
     */
    function format_yen(int $cents): string
    {
        $yen = $cents / 100;
        $isNegative = $yen < 0;
        $absoluteYen = abs($yen);
        
        // Use floor() to round down (10.5 -> 10, 10.9 -> 10)
        $roundedYen = floor($absoluteYen);
        
        $formatted = '¥' . number_format($roundedYen);
        
        return $isNegative ? '-' . $formatted : $formatted;
    }
}

if (!function_exists('currency')) {
    /**
     * Format a price in cents to currency format.
     *
     * @param int $cents
     * @return string
     */
    function currency(int $cents): string
    {
        $amount = $cents / 100;
        $isNegative = $amount < 0;
        $absoluteAmount = abs($amount);
        
        // Use floor() to round down (10.5 -> 10, 10.9 -> 10)
        $roundedAmount = floor($absoluteAmount);
        
        $formatted = '¥' . number_format($roundedAmount);
        
        return $isNegative ? '-' . $formatted : $formatted;
    }
}

if (!function_exists('remaining_attempts')) {
    /**
     * Calculate remaining download attempts for a grant.
     *
     * @param \App\Models\DownloadGrant $grant
     * @return int
     */
    function remaining_attempts(\App\Models\DownloadGrant $grant): int
    {
        // Check if grant is expired
        if ($grant->expires_at && $grant->expires_at < now()) {
            return 0;
        }
        
        return max(0, $grant->max_downloads - $grant->downloads_used);
    }
}