<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Provider;
use App\Models\Service;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncProviderServices extends Command
{
    protected $signature = 'provider:sync-services {provider_id?}';

    protected $description = 'Auto-sync services from all active providers with auto_sync enabled. Imports new services & updates existing ones.';

    public function handle()
    {
        $query = Provider::where('status', 'active')
            ->where('auto_sync', true);

        if ($providerId = $this->argument('provider_id')) {
            $query->where('id', $providerId);
        }

        $providers = $query->get();

        if ($providers->isEmpty()) {
            $this->info('No providers with auto_sync enabled.');
            return self::SUCCESS;
        }

        $profitMargin = (float) Setting::get('profit_margin', 50);
        $multiplier = 1 + ($profitMargin / 100);

        $this->info("Found {$providers->count()} provider(s) to sync. Profit margin: {$profitMargin}%");

        foreach ($providers as $provider) {
            $this->syncProvider($provider, $multiplier);
        }

        $this->info('All provider service syncs completed.');
        return self::SUCCESS;
    }

    private function syncProvider(Provider $provider, float $multiplier)
    {
        $this->info("Syncing services from: {$provider->name}");

        try {
            // Use longer timeout for services list (large payload, 500KB+)
            $response = Http::timeout(120)->asForm()->post($provider->api_url, [
                'key' => $provider->api_key,
                'action' => 'services',
            ]);

            if (!$response->successful()) {
                $provider->last_sync_failed = now();
                $provider->failed_requests_count += 1;
                $provider->last_sync_error = "Auto-sync failed: HTTP {$response->status()}";
                $provider->save();

                $this->error("HTTP {$response->status()} from {$provider->name}");
                Log::warning("Auto-sync failed for [{$provider->name}]: HTTP {$response->status()}");
                return;
            }

            $services = $response->json();

            if (!is_array($services)) {
                $provider->last_sync_failed = now();
                $provider->failed_requests_count += 1;
                $provider->last_sync_error = "Auto-sync failed: Invalid response format from provider.";
                $provider->save();

                $this->error("Invalid response from {$provider->name}");
                Log::warning("Auto-sync invalid response from [{$provider->name}]");
                return;
            }

            $importedCount = 0;
            $enabledPlatforms = \App\Services\PlatformHelper::getEnabledPlatforms();
            $updatedCount = 0;
            $categoryCache = [];

            $srvIndex = 0;
            foreach ($services as $srv) {
                $providerServiceId = (string) ($srv['service'] ?? '');
                if (!$providerServiceId) continue;
                $srvIndex++;

                $srvRawName = $srv['name'] ?? '';
                $catRawName = $srv['category'] ?? '';

                $isAllowed = \App\Services\PlatformHelper::isPlatformEnabled($srvRawName, $catRawName, $enabledPlatforms);

                $existing = Service::where('provider_id', $provider->id)
                    ->where('provider_service_id', $providerServiceId)
                    ->first();

                $rate = (float) ($srv['rate'] ?? 0);
                $sellingPrice = $rate * $multiplier;
                $avgTime = $srv['time'] ?? $srv['average_time'] ?? null;

                if (!$isAllowed) {
                    if ($existing) {
                        $existing->provider_rate = $rate;
                        $existing->price_per_k = $sellingPrice;
                        if ($existing->status === 'active') {
                            $existing->status = 'inactive';
                        }
                        $existing->save();
                    }
                    continue;
                }

                $categoryName = trim($srv['category'] ?? 'General');
                $cleanCategoryName = \App\Services\BrandingSanitizer::clean($categoryName, $provider->name) ?: 'General';

                $isJunkCat = preg_match('/\b(pvt|private|testing|test|dummy|junk|trash)\b/i', $cleanCategoryName)
                    || preg_match('/end\s*x+/i', $cleanCategoryName)
                    || preg_match('/❌\s*end/i', $cleanCategoryName)
                    || preg_match('/inttttt/i', $cleanCategoryName);

                if ($isJunkCat) {
                    if ($existing) {
                        $existing->provider_rate = $rate;
                        $existing->price_per_k = $sellingPrice;
                        if ($existing->status === 'active') {
                            $existing->status = 'inactive';
                        }
                        $existing->save();
                    }
                    continue;
                }

                if (!isset($categoryCache[$cleanCategoryName])) {
                    $category = Category::where('original_name', $cleanCategoryName)
                        ->orWhere('name', $cleanCategoryName)
                        ->first();

                    if (!$category) {
                        // Place new category at end of sort order (max sort order + 1)
                        // Do NOT override existing category sort orders set by admin
                        $nextCatOrder = (Category::max('sort_order') ?? 0) + 1;

                        $category = Category::create([
                            'name' => $cleanCategoryName,
                            'original_name' => $cleanCategoryName,
                            'is_custom_name' => false,
                            'status' => 'active',
                            'sort_order' => $nextCatOrder,
                        ]);
                    } else {
                        // Preserve existing sort_order set by admin
                        $category->original_name = $cleanCategoryName;
                        if (!$category->is_custom_name) {
                            $category->name = $cleanCategoryName;
                        }
                        if ($category->status === 'inactive') {
                            $category->status = 'active';
                        }
                        $category->save();
                    }
                    $categoryCache[$cleanCategoryName] = $category->id;
                }
                $catId = $categoryCache[$cleanCategoryName];

                $cleanSrvName = \App\Services\BrandingSanitizer::clean($srv['name'] ?? 'Unnamed service', $provider->name);

                // Check if provider explicitly marked service as disabled/inactive in API payload, name, category, or rate
                $isProviderDisabled = false;
                if (isset($srv['status'])) {
                    $st = strtolower((string)$srv['status']);
                    if (in_array($st, ['inactive', 'disabled', 'off', '0', 'false'], true)) {
                        $isProviderDisabled = true;
                    }
                }
                if (isset($srv['disabled']) && ($srv['disabled'] === true || $srv['disabled'] === 1 || $srv['disabled'] === '1')) {
                    $isProviderDisabled = true;
                }
                if ($rate <= 0) {
                    $isProviderDisabled = true;
                }
                $disabledPattern = '/\b(disabled|not\s*working|closed|paused|stopped|temp\s*off|off|offline|maintenance|dont\s*use|do\s*not\s*use|temp\s*down|down)\b|\[\s*(off|disabled|paused|closed|stopped|down|temp\s*off)\s*\]|[❌🚫🛑⛔]/i';
                if (preg_match($disabledPattern, $srvRawName) || preg_match($disabledPattern, $catRawName)) {
                    $isProviderDisabled = true;
                }


                if ($existing) {
                    $existing->category_id = $catId;
                    $existing->original_name = $cleanSrvName;
                    if (!$existing->is_custom_name) {
                        $existing->name = $cleanSrvName;
                    }
                    if (isset($srv['description'])) {
                        $existing->description = \App\Services\BrandingSanitizer::clean($srv['description'], $provider->name);
                    }
                    $existing->provider_rate = $rate;
                    $existing->price_per_k = $sellingPrice;
                    $existing->min_quantity = (int) ($srv['min'] ?? 10);
                    $existing->max_quantity = (int) ($srv['max'] ?? 10000);
                    if ($isProviderDisabled && $existing->status === 'active') {
                        $existing->status = 'inactive';
                    }
                    if ($avgTime) {
                        $existing->average_time = $avgTime;
                    }
                    $existing->save();
                    $updatedCount++;
                    continue;
                }

                // Import new service
                $nextSrvOrder = (Service::where('category_id', $catId)->max('sort_order') ?? 0) + 1;
                Service::create([
                    'category_id' => $catId,
                    'name' => $cleanSrvName,
                    'original_name' => $cleanSrvName,
                    'is_custom_name' => false,
                    'description' => \App\Services\BrandingSanitizer::clean($srv['description'] ?? null, $provider->name),
                    'price_per_k' => $sellingPrice,
                    'min_quantity' => (int) ($srv['min'] ?? 10),
                    'max_quantity' => (int) ($srv['max'] ?? 10000),
                    'status' => $isProviderDisabled ? 'inactive' : 'active',
                    'sort_order' => $nextSrvOrder,
                    'provider_id' => $provider->id,
                    'provider_service_id' => $providerServiceId,
                    'provider_rate' => $rate,
                    'average_time' => $avgTime,
                ]);
                $importedCount++;
            }

            $totalApi = count($services);

            // Collect valid provider service IDs returned by API
            $apiServiceIds = array_filter(array_map(function ($s) {
                return (string) ($s['service'] ?? '');
            }, $services));

            // Auto-deactivate services that were removed/disabled by provider
            $disabledCount = 0;
            if (!empty($apiServiceIds)) {
                $disabledCount = Service::where('provider_id', $provider->id)
                    ->whereNotIn('provider_service_id', $apiServiceIds)
                    ->where('status', 'active')
                    ->update(['status' => 'inactive']);
            }

            // Auto-delete empty categories that have 0 services
            Category::doesntHave('services')->delete();

            // Update provider sync status to successful
            $provider->last_sync_success = now();
            $provider->last_sync_error = null;
            $provider->failed_requests_count = 0;
            $provider->save();

            \App\Services\PlatformHelper::clearCache();

            $this->info("  ✓ {$provider->name}: Fetched {$totalApi} | New: {$importedCount} | Updated: {$updatedCount} | Disabled: {$disabledCount}");

            Log::info("Auto-sync [{$provider->name}]: Fetched {$totalApi}, Imported {$importedCount}, Updated {$updatedCount}, Disabled {$disabledCount}");

        } catch (\Exception $e) {
            $provider->last_sync_failed = now();
            $provider->failed_requests_count += 1;
            $provider->last_sync_error = 'Auto-sync failed: ' . $e->getMessage();
            $provider->save();

            $this->error("  ✗ {$provider->name}: " . $e->getMessage());
            Log::error("Auto-sync failed [{$provider->name}]: " . $e->getMessage());
        }
    }
}
