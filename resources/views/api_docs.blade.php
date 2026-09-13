@extends(Auth::check() ? 'layouts.app' : 'layouts.landing')

@section('title', 'Reseller API Documentation - ' . App\Models\Setting::get('site_name', 'SMM Panel'))
@section('page_header', 'Developer API')

@section('content')
<div style="padding: {{ Auth::check() ? '0' : '3rem 5%' }}; max-width: 1000px; margin: 0 auto;">

    @if(!Auth::check())
        <div style="text-align: center; margin-bottom: 3rem;">
            <h2 style="font-size: 2.2rem; font-weight: 800; margin-bottom: 10px;">Developer <span class="text-gradient">API Documentation</span></h2>
            <p style="color: var(--text-secondary);">Integrate our automated SMM services directly into your own website.</p>
        </div>
    @endif

    @auth
        <!-- API Key Display Box for Logged-in Users -->
        <div class="glass custom-card" style="border-left: 4px solid var(--color-primary); margin-bottom: 2.5rem;">
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 10px;"><i class="fa-solid fa-key text-gradient" style="margin-right: 8px;"></i> Your Private API Access Key</h3>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem;">
                Keep this key secret. Do not share it. Use it as the <code>key</code> parameter in all your API requests.
            </p>
            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <input type="password" id="apiKeyVal" class="form-control" value="{{ Auth::user()->api_key }}" aria-label="Your Private API Access Key" readonly style="font-family: monospace; letter-spacing: 2px; flex: 1; min-width: 250px;">
                <button onclick="toggleKey()" class="btn-outline" style="padding: 12px 18px;" title="Show/Hide Key" aria-label="Show or hide API key">
                    <i id="toggleEye" class="fa-solid fa-eye"></i>
                </button>
                <button onclick="copyKey()" class="btn-gradient" style="padding: 12px 22px; white-space: nowrap;">
                    <i class="fa-solid fa-copy" style="margin-right: 8px;"></i> Copy Key
                </button>
                <button id="regenBtn" onclick="regenerateApiKey()" class="btn-outline" style="padding: 12px 20px; white-space: nowrap; border-color: rgba(239, 68, 68, 0.4); color: #f87171;" title="Generate Brand New API Key">
                    <i id="regenSpin" class="fa-solid fa-rotate" style="margin-right: 8px;"></i> Regenerate Key
                </button>
            </div>
        </div>
    @endauth

    <!-- Base Configuration Details -->
    <div class="glass custom-card">
        <h3 class="card-title"><i class="fa-solid fa-circle-info"></i> API General Parameters</h3>
        <table class="custom-table" style="margin-bottom: 1.5rem;">
            <thead>
                <tr>
                    <th style="width: 180px;">Parameter</th>
                    <th style="width: 120px;">Type</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>API URL</code></td>
                    <td><span class="badge badge-inprogress">POST</span></td>
                    <td style="font-weight: 600; font-family: monospace; color: var(--color-info);">
                        {{ url('/api/v1') }}
                    </td>
                </tr>
                <tr>
                    <td><code>key</code></td>
                    <td><code>string</code></td>
                    <td>Your unique api key.</td>
                </tr>
                <tr>
                    <td><code>action</code></td>
                    <td><code>string</code></td>
                    <td>Supported values: <code>services</code>, <code>add</code>, <code>status</code>, <code>balance</code></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- API Actions Documentation -->
    <div class="glass custom-card">
        <h3 class="card-title"><i class="fa-solid fa-gears"></i> 1. Retrieve Active Services</h3>
        <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 1.5rem;">
            Fetch list of all available services on the SMM Panel.
        </p>
        <div style="background: rgba(0,0,0,0.3); border-radius: var(--radius-md); padding: 1.5rem; font-family: monospace; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 2rem; overflow-x: auto;">
            <strong>Request POST:</strong><br>
            action: "services"<br>
            key: "YOUR_API_KEY"<br><br>
            <strong>Response JSON:</strong><br>
            [<br>
            &nbsp;&nbsp;{<br>
            &nbsp;&nbsp;&nbsp;&nbsp;"service": 1,<br>
            &nbsp;&nbsp;&nbsp;&nbsp;"name": "Instagram Followers [High Quality]",<br>
            &nbsp;&nbsp;&nbsp;&nbsp;"type": "Default",<br>
            &nbsp;&nbsp;&nbsp;&nbsp;"category": "Instagram Followers",<br>
            &nbsp;&nbsp;&nbsp;&nbsp;"rate": 55.40,<br>
            &nbsp;&nbsp;&nbsp;&nbsp;"min": 100,<br>
            &nbsp;&nbsp;&nbsp;&nbsp;"max": 10000,<br>
            &nbsp;&nbsp;&nbsp;&nbsp;"description": "Instant start, lifetime guarantee."<br>
            &nbsp;&nbsp;}<br>
            ]
        </div>

        <h3 class="card-title" style="margin-top: 3rem;"><i class="fa-solid fa-cart-plus"></i> 2. Place New Order</h3>
        <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 1.5rem;">
            Submit an engagement order. Funds are deducted automatically from your user balance.
        </p>
        <div style="background: rgba(0,0,0,0.3); border-radius: var(--radius-md); padding: 1.5rem; font-family: monospace; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 2rem; overflow-x: auto;">
            <strong>Request POST:</strong><br>
            action: "add"<br>
            key: "YOUR_API_KEY"<br>
            service: 1<br>
            link: "https://instagram.com/p/yourpost"<br>
            quantity: 1000<br><br>
            <strong>Response JSON:</strong><br>
            {<br>
            &nbsp;&nbsp;"order": 45892<br>
            }
        </div>

        <h3 class="card-title" style="margin-top: 3rem;"><i class="fa-solid fa-magnifying-glass-chart"></i> 3. Check Order Status</h3>
        <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 1.5rem;">
            Query the tracking status, remains, and start count of an order. You can also pass a comma-separated list of order IDs in the <code>orders</code> parameter to check multiple status parameters simultaneously.
        </p>
        <div style="background: rgba(0,0,0,0.3); border-radius: var(--radius-md); padding: 1.5rem; font-family: monospace; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 2rem; overflow-x: auto;">
            <strong>Request POST:</strong><br>
            action: "status"<br>
            key: "YOUR_API_KEY"<br>
            order: 45892<br><br>
            <strong>Response JSON:</strong><br>
            {<br>
            &nbsp;&nbsp;"charge": 55.4000,<br>
            &nbsp;&nbsp;"start_count": 1420,<br>
            &nbsp;&nbsp;"status": "Processing",<br>
            &nbsp;&nbsp;"remains": 1000,<br>
            &nbsp;&nbsp;"currency": "INR"<br>
            }
        </div>

        <h3 class="card-title" style="margin-top: 3rem;"><i class="fa-solid fa-wallet"></i> 4. Check Account Balance</h3>
        <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 1.5rem;">
            Retrieve your current balance and panel base currency.
        </p>
        <div style="background: rgba(0,0,0,0.3); border-radius: var(--radius-md); padding: 1.5rem; font-family: monospace; font-size: 0.85rem; color: var(--text-secondary); overflow-x: auto;">
            <strong>Request POST:</strong><br>
            action: "balance"<br>
            key: "YOUR_API_KEY"<br><br>
            <strong>Response JSON:</strong><br>
            {<br>
            &nbsp;&nbsp;"balance": 750.4500,<br>
            &nbsp;&nbsp;"currency": "INR"<br>
            }
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    function toggleKey() {
        const keyInput = document.getElementById('apiKeyVal');
        const toggleEye = document.getElementById('toggleEye');
        if (keyInput.type === 'password') {
            keyInput.type = 'text';
            toggleEye.className = 'fa-solid fa-eye-slash';
        } else {
            keyInput.type = 'password';
            toggleEye.className = 'fa-solid fa-eye';
        }
    }
    
    function copyKey() {
        const keyInput = document.getElementById('apiKeyVal');
        keyInput.select();
        keyInput.setSelectionRange(0, 99999);
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(keyInput.value).catch(() => {
                document.execCommand('copy');
            });
        } else {
            document.execCommand('copy');
        }
        alert('API Key copied to clipboard!');
    }

    function regenerateApiKey() {
        if (!confirm('Warning: Regenerating your API key will generate a brand new key and invalidate your current key for all integrations. Continue?')) {
            return;
        }

        const regenBtn = document.getElementById('regenBtn');
        const regenSpin = document.getElementById('regenSpin');
        const keyInput = document.getElementById('apiKeyVal');

        if (regenSpin) regenSpin.classList.add('fa-spin');
        if (regenBtn) regenBtn.disabled = true;

        fetch("{{ route('profile.api_key') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (regenSpin) regenSpin.classList.remove('fa-spin');
            if (regenBtn) regenBtn.disabled = false;

            if (data.status === 'success' && data.api_key) {
                keyInput.value = data.api_key;
                alert('✓ New API Key generated successfully! Your key has been updated.');
            } else {
                location.reload();
            }
        })
        .catch(err => {
            if (regenSpin) regenSpin.classList.remove('fa-spin');
            if (regenBtn) regenBtn.disabled = false;
            location.reload();
        });
    }
</script>
@endsection
