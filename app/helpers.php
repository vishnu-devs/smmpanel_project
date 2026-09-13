<?php

if (!class_exists(\App\Services\OrderStatusSyncService::class)) {
    $syncServiceFile = __DIR__ . '/Services/OrderStatusSyncService.php';
    if (file_exists($syncServiceFile)) {
        require_once $syncServiceFile;
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format a currency amount with dynamic precision.
     * - Standard amounts (>= 0.01): Formatted with 2 decimal places e.g. 10.50, 0.50
     * - Micro amounts (< 0.01): Formatted with up to 4 decimal places e.g. 0.008, 0.0008
     *
     * @param float|int|string|null $amount
     * @return string
     */
    function format_currency($amount): string
    {
        $val = (float) $amount;
        if ($val == 0) {
            return '0.00';
        }

        $abs = abs($val);

        if ($abs < 0.01) {
            // High-precision micro formatting up to 4 decimal places
            $formatted = number_format($val, 4, '.', '');
            $trimmed = rtrim(rtrim($formatted, '0'), '.');
            return ($trimmed === '0' || $trimmed === '' || $trimmed === '-0') ? '0.00' : $trimmed;
        }

        return number_format($val, 2, '.', '');
    }
}
