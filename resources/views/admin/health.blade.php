@extends('layouts.app')

@section('title', 'Cron Jobs & System Diagnostics - RishiSMM')
@section('page_header', 'System Diagnostics & Cron Endpoints')

@section('content')
<div style="max-width: 1050px; margin: 0 auto;">

    <!-- Diagnostics Overview Grid -->
    <div class="card-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 2rem;">
        
        <!-- System Engine Box -->
        <div class="glass custom-card" style="border-top: 4px solid var(--color-primary);">
            <h3 class="card-title" style="margin-bottom: 15px;"><i class="fa-solid fa-server text-gradient"></i> Core Software Engine</h3>
            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 0.9rem;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                    <span style="color: var(--text-secondary);">PHP Version</span>
                    <strong style="color: var(--text-primary);">v{{ $health['php'] }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                    <span style="color: var(--text-secondary);">Laravel Framework</span>
                    <strong style="color: var(--text-primary);">v{{ $health['laravel'] }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 4px;">
                    <span style="color: var(--text-secondary);">MySQL Database</span>
                    <strong style="color: var(--text-primary);">v{{ $health['mysql'] }}</strong>
                </div>
            </div>
        </div>

        <!-- Hosting Disk Usage Box -->
        <div class="glass custom-card" style="border-top: 4px solid var(--color-info);">
            <h3 class="card-title" style="margin-bottom: 15px;"><i class="fa-solid fa-hard-drive" style="color: var(--color-info);"></i> Storage Capacity</h3>
            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 0.9rem;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                    <span style="color: var(--text-secondary);">Free Storage Space</span>
                    <strong style="color: var(--color-info);">{{ $health['disk_free'] }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                    <span style="color: var(--text-secondary);">Total Storage Space</span>
                    <strong style="color: var(--text-primary);">{{ $health['disk_total'] }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 4px;">
                    <span style="color: var(--text-secondary);">Queue Engine</span>
                    <strong style="color: var(--text-primary); text-transform: uppercase;">{{ $health['queue_status'] }}</strong>
                </div>
            </div>
        </div>

        <!-- Cron Task Scheduler Status Box -->
        <div class="glass custom-card" style="border-top: 4px solid var(--color-success);">
            <h3 class="card-title" style="margin-bottom: 15px;"><i class="fa-solid fa-clock" style="color: var(--color-success);"></i> Cron Health Monitor</h3>
            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 0.9rem;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                    <span style="color: var(--text-secondary);">Last Execution</span>
                    <strong style="color: var(--color-success);">{{ $health['last_cron'] }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                    <span style="color: var(--text-secondary);">Failed Jobs in Queue</span>
                    <strong style="color: var(--text-primary);">{{ $health['failed_jobs'] }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 4px;">
                    <span style="color: var(--text-secondary);">Failed Provider Actions</span>
                    <strong style="color: var(--color-danger);">{{ $health['failed_provider_requests'] }}</strong>
                </div>
            </div>
        </div>

    </div>

    <!-- MAIN CRON ENDPOINTS & AUTOMATION SETUP SECTION -->
    <div class="glass custom-card" style="margin-bottom: 2rem; border-top: 4px solid var(--color-primary); position: relative;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
            <div>
                <h3 class="card-title" style="margin-bottom: 4px;">
                    <i class="fa-solid fa-bolt text-gradient"></i> Job Cron Endpoints & Automation Setup
                </h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 0;">
                    Automate order status tracking, SMM provider balance sync, daily scratch cards, and system cleanup.
                </p>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <form action="{{ route('admin.cron.run_manual') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-gradient" style="padding: 10px 18px; font-size: 0.85rem; background: var(--grad-emerald); font-weight: 700; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-play"></i> Run Cron Manually Now
                    </button>
                </form>

                <form action="{{ route('admin.cron.regenerate_key') }}" method="POST" onsubmit="return confirm('Regenerate Cron Secret Key? Existing external web cron services using the old key will fail until updated.');">
                    @csrf
                    <button type="submit" class="btn-outline" style="padding: 10px 15px; font-size: 0.85rem; border-radius: var(--radius-sm);">
                        <i class="fa-solid fa-key"></i> Regenerate Secret Key
                    </button>
                </form>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 20px;">

            <!-- Method 1: Web Cron Endpoint URL -->
            <div style="background: rgba(0, 0, 0, 0.25); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="badge badge-completed" style="font-size: 0.75rem; background: var(--grad-purple); color: white;">HTTP Web Cron (Recommended)</span>
                        <strong style="font-size: 0.95rem; color: var(--text-primary);">Web Cron Endpoint URL</strong>
                    </div>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">For cron-job.org, EasyCron, UptimeRobot, or Web Browsers</span>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 10px;">
                    Use this URL if your hosting provider removed or disabled CLI Crons. You can set this URL to trigger every 1 minute on free external cron services like <a href="https://cron-job.org" target="_blank" style="color: var(--color-info); text-decoration: underline;">cron-job.org</a> or inside cPanel <code>curl</code>.
                </p>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <input type="text" id="cronWebUrlInput" value="{{ $health['cron_web_url'] }}" readonly class="form-control" style="flex: 1; min-width: 280px; font-family: monospace; font-size: 0.85rem; background: rgba(0,0,0,0.4); color: var(--color-info); font-weight: bold; padding: 10px 14px;">
                    <button type="button" onclick="copyToClipboard('cronWebUrlInput', 'Web Cron URL')" class="btn-gradient" style="padding: 10px 18px; font-size: 0.82rem; background: var(--grad-purple); white-space: nowrap;">
                        <i class="fa-solid fa-copy"></i> Copy Web Cron URL
                    </button>
                    <a href="{{ $health['cron_web_url'] }}" target="_blank" class="btn-outline" style="padding: 10px 14px; font-size: 0.82rem; white-space: nowrap;">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Test in Browser
                    </a>
                </div>
            </div>

            <!-- Method 2: cPanel Curl / Wget Command -->
            <div style="background: rgba(0, 0, 0, 0.25); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="badge badge-completed" style="font-size: 0.75rem; background: #0284c7; color: white;">cPanel HTTP Cron</span>
                        <strong style="font-size: 0.95rem; color: var(--text-primary);">cPanel Curl Command</strong>
                    </div>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">For cPanel > Cron Jobs tab (without CLI PHP dependency)</span>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 10px;">
                    If your hosting provider removed direct PHP CLI commands, paste this <code>curl</code> command in your <strong>cPanel > Cron Jobs</strong> tab (set to Once Per Minute <code>* * * * *</code>):
                </p>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <input type="text" id="cronCurlCmdInput" value="{{ $health['cron_curl_cmd'] }}" readonly class="form-control" style="flex: 1; min-width: 280px; font-family: monospace; font-size: 0.85rem; background: rgba(0,0,0,0.4); color: #38bdf8; font-weight: bold; padding: 10px 14px;">
                    <button type="button" onclick="copyToClipboard('cronCurlCmdInput', 'cPanel Curl Command')" class="btn-outline" style="padding: 10px 18px; font-size: 0.82rem; white-space: nowrap;">
                        <i class="fa-solid fa-copy"></i> Copy Curl Command
                    </button>
                </div>
            </div>

            <!-- Method 3: Standard cPanel CLI PHP Command -->
            <div style="background: rgba(0, 0, 0, 0.25); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="badge badge-completed" style="font-size: 0.75rem; background: #16a34a; color: white;">cPanel CLI Command</span>
                        <strong style="font-size: 0.95rem; color: var(--text-primary);">cPanel Artisan Schedule Command</strong>
                    </div>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">Standard Laravel CLI Command</span>
                </div>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 10px;">
                    If CLI PHP is enabled on your server, paste this standard command in cPanel:
                </p>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <input type="text" id="cronCliCmdInput" value="{{ $health['cron_cli_cmd'] }}" readonly class="form-control" style="flex: 1; min-width: 280px; font-family: monospace; font-size: 0.85rem; background: rgba(0,0,0,0.4); color: #4ade80; font-weight: bold; padding: 10px 14px;">
                    <button type="button" onclick="copyToClipboard('cronCliCmdInput', 'cPanel CLI Command')" class="btn-outline" style="padding: 10px 18px; font-size: 0.82rem; white-space: nowrap;">
                        <i class="fa-solid fa-copy"></i> Copy CLI Command
                    </button>
                </div>
            </div>

        </div>

    </div>

    <!-- System Cache & Performance Optimization Box -->
    <div class="glass custom-card" style="margin-bottom: 2rem; border-top: 4px solid var(--color-warning, #f59e0b);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h3 class="card-title" style="margin-bottom: 6px;"><i class="fa-solid fa-broom text-gradient"></i> System Cache & Optimization</h3>
                <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 0;">
                    Flush precompiled Blade templates, cached routes, configuration, application cache, and reset PHP OPcache immediately.
                </p>
            </div>
            <form action="{{ route('admin.cache.clear') }}" method="POST" onsubmit="return confirm('Clear system cache, compiled routes, views, config, and reset OPcache?');">
                @csrf
                <button type="submit" class="btn-gradient" style="padding: 10px 22px; font-weight: 700; background: var(--grad-purple); display: inline-flex; align-items: center; gap: 8px; border: none; cursor: pointer;">
                    <i class="fa-solid fa-broom"></i> Clear System Cache & OPcache
                </button>
            </form>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    function copyToClipboard(elementId, label) {
        const copyText = document.getElementById(elementId);
        if (!copyText) return;
        
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices
        
        navigator.clipboard.writeText(copyText.value).then(() => {
            alert(label + ' copied to clipboard!');
        }).catch(err => {
            // Fallback for older browsers
            document.execCommand('copy');
            alert(label + ' copied to clipboard!');
        });
    }
</script>
@endsection
