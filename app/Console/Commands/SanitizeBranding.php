<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;
use App\Models\Service;
use App\Models\Provider;
use App\Services\BrandingSanitizer;

class SanitizeBranding extends Command
{
    protected $signature = 'db:sanitize-branding';
    protected $description = 'Clean all third-party provider branding (e.g. SMMBIN, SMM BIN) from categories and services, replacing with RishiSMM.';

    public function handle()
    {
        $this->info('Starting database branding sanitization...');

        $categories = Category::all();
        $catUpdated = 0;
        foreach ($categories as $cat) {
            $cleanName = BrandingSanitizer::clean($cat->name);
            if ($cleanName !== $cat->getRawOriginal('name')) {
                $cat->name = $cleanName;
                $cat->save();
                $catUpdated++;
            }
        }

        $services = Service::with('provider')->get();
        $srvUpdated = 0;
        foreach ($services as $srv) {
            $providerName = $srv->provider ? $srv->provider->name : null;
            $cleanName = BrandingSanitizer::clean($srv->getRawOriginal('name'), $providerName);
            $cleanDesc = BrandingSanitizer::clean($srv->getRawOriginal('description'), $providerName);

            if ($cleanName !== $srv->getRawOriginal('name') || $cleanDesc !== $srv->getRawOriginal('description')) {
                $srv->name = $cleanName;
                $srv->description = $cleanDesc;
                $srv->save();
                $srvUpdated++;
            }
        }

        $this->info("Sanitization complete! Cleaned {$catUpdated} categories and {$srvUpdated} services.");
        return self::SUCCESS;
    }
}
