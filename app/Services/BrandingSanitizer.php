<?php

namespace App\Services;

class BrandingSanitizer
{
    /**
     * Automatically clean third-party SMM provider branding from service/category names & descriptions
     */
    public static function clean(?string $text, ?string $providerName = null): ?string
    {
        if (empty($text)) {
            return $text;
        }

        // 1. Clean SMMBIN variations (handles SMM BIN, SMM-BIN, SMM_BIN, SMMBIN, smmbin_smm_panel, smmbin.com, etc.)
        $patterns = [
            '/smm\s*bin/i',
            '/smm[-_]bin/i',
            '/smmbin/i',
        ];

        $text = preg_replace($patterns, 'RishiSMM', $text);

        // 2. Clean specific provider name if provided
        if (!empty($providerName) && strlen(trim($providerName)) > 2) {
            $pName = trim($providerName);
            $text = preg_replace('/' . preg_quote($pName, '/') . '/i', 'RishiSMM', $text);

            // Replace spaced camel case version e.g. "Main Provider" if provider name is "MainProvider"
            $spaced = preg_replace('/([a-z])([A-Z])/', '$1 $2', $pName);
            if ($spaced !== $pName) {
                $text = preg_replace('/' . preg_quote($spaced, '/') . '/i', 'RishiSMM', $text);
            }
        }

        return $text;
    }
}
