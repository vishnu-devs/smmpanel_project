@extends('layouts.app')

@section('title', 'Manage Users - ' . App\Models\Setting::get('site_name', 'SMM Panel'))
@section('page_header', 'User Management')

@section('styles')
<style>
    .desktop-only-table {
        display: block;
    }
    .mobile-only-users {
        display: none;
    }
    @media (max-width: 768px) {
        .desktop-only-table {
            display: none !important;
        }
        .mobile-only-users {
            display: flex !important;
            flex-direction: column;
            gap: 8px;
        }
    }
</style>
@endsection

@section('content')
<div style="max-width: 1250px; margin: 0 auto;">

    <!-- Users List Column -->
    <div class="glass custom-card" style="margin-bottom: 2rem;">
            <h3 class="card-title"><i class="fa-solid fa-users"></i> Platform Users</h3>
            
            <!-- Search bar -->
            <form id="userSearchForm" action="{{ route('admin.users') }}" method="GET" style="margin-bottom: 1.5rem; display: flex; gap: 10px;" onsubmit="return false;">
                <input type="text" id="userSearchInput" name="search" class="form-control" placeholder="Search by name, email, or WhatsApp..." value="{{ $search }}" style="padding: 10px 16px;">
                <button type="button" id="userSearchBtn" class="btn-gradient" style="padding: 10px 20px;">
                    <i class="fa-solid fa-magnifying-glass"></i> Search
                </button>
                <a href="{{ route('admin.users') }}" id="userClearBtn" class="btn-outline" style="padding: 10px 15px; {{ $search ? '' : 'display: none;' }}">Clear</a>
            </form>

            <!-- Table Container for AJAX Live Refresh -->
            <div id="usersTableContainer" style="position: relative; min-height: 200px;">
                @include('admin.partials.users_table')
            </div>
    </div>

    @push('modals')
    <!-- Edit User Modal Overlay -->
    <div id="editUserModal" class="custom-modal" onclick="if(event.target===this) closeEditUserModal()">
        <div class="custom-modal-content animate-fade-in">
            <div class="custom-modal-header">
                <div class="custom-modal-header-text">
                    <h3 class="custom-modal-title">
                        <i class="fa-solid fa-user-gear text-gradient" style="margin-right: 6px;"></i> Edit User Settings
                    </h3>
                    <p class="custom-modal-subtitle">Modify role, account status, balance, or password</p>
                </div>
                <button type="button" class="custom-modal-close" onclick="closeEditUserModal()" title="Close">&times;</button>
            </div>
            <form id="editUserForm" action="" method="POST" onsubmit="return confirm('Apply these settings and wallet modifications to the user account?')">
                @csrf
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="custom-modal-body" style="display: flex; flex-direction: column; gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" style="font-size: 0.8rem; font-weight: 600;">Client Name</label>
                        <input type="text" id="edit_name" class="form-control" readonly style="background: rgba(255,255,255,0.02); color: var(--text-muted); font-size: 0.88rem;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="edit_role" class="form-label" style="font-size: 0.8rem; font-weight: 600;">System Role</label>
                            <select name="role" id="edit_role" class="form-control" required style="font-size: 0.85rem;">
                                <option value="user">Client (Standard User)</option>
                                <option value="admin">Administrator (Full Access)</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="edit_status" class="form-label" style="font-size: 0.8rem; font-weight: 600;">Account Status</label>
                            <select name="status" id="edit_status" class="form-control" required style="font-size: 0.85rem;">
                                <option value="active">Active (Access Allowed)</option>
                                <option value="suspended">Suspended (Banned/Blocked)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Wallet Funds Adjustments -->
                    <div class="modal-info-summary" style="margin-bottom: 0;">
                        <h4 style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin: 0 0 8px 0; color: var(--color-info); display: flex; align-items: center; justify-content: space-between;">
                            <span><i class="fa-solid fa-wallet" style="margin-right: 6px;"></i> Adjust User Balance</span>
                            <span style="font-size: 0.82rem; color: var(--text-secondary); text-transform: none;">Current: <strong id="edit_current_balance" class="text-gradient">₹0.00</strong></span>
                        </h4>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="edit_balance_action" class="form-label" style="font-size: 0.78rem;">Action Type</label>
                                <select name="balance_action" id="edit_balance_action" class="form-control" style="font-size: 0.82rem; padding: 6px 10px;">
                                    <option value="no_action">No Action</option>
                                    <option value="add">Add Funds (+)</option>
                                    <option value="subtract">Subtract Funds (-)</option>
                                    <option value="set">Set New Balance (=)</option>
                                </select>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="edit_balance_amount" class="form-label" style="font-size: 0.78rem;">Amount (INR)</label>
                                <input type="number" name="balance_amount" id="edit_balance_amount" class="form-control" placeholder="0.00" step="any" min="0" style="font-size: 0.82rem; padding: 6px 10px;">
                            </div>
                        </div>
                    </div>

                    <!-- Reset Password -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="edit_password" class="form-label" style="font-size: 0.8rem; font-weight: 600;">Reset Password</label>
                        <input type="password" name="password" id="edit_password" class="form-control" placeholder="Enter new password to reset (leave blank to keep current)" style="font-size: 0.85rem;">
                    </div>
                </div>
                <div class="custom-modal-footer">
                    <button type="button" class="btn-outline" style="height: 40px; padding: 0 18px; font-size: 0.88rem;" onclick="closeEditUserModal()">Cancel</button>
                    <button type="submit" class="btn-gradient" style="height: 40px; padding: 0 22px; font-size: 0.88rem; font-weight: bold; background: var(--grad-purple);">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    @endpush

</div>
@endsection

@section('scripts')
<script>
    function selectUserForEdit(user) {
        const form = document.getElementById('editUserForm');
        
        // Populate inputs
        document.getElementById('edit_user_id').value = user.id;
        document.getElementById('edit_name').value = user.name;
        document.getElementById('edit_role').value = user.role;
        document.getElementById('edit_status').value = user.status;
        document.getElementById('edit_current_balance').innerText = '₹' + parseFloat(user.balance).toFixed(2);
        
        // Reset balance action fields
        document.getElementById('edit_balance_action').value = 'no_action';
        document.getElementById('edit_balance_amount').value = '';
        document.getElementById('edit_password').value = '';
        
        // Update form action dynamically
        form.action = `/admin/users/${user.id}/edit`;
        
        const modal = document.getElementById('editUserModal');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeEditUserModal() {
        document.getElementById('editUserModal').classList.remove('show');
        document.body.style.overflow = '';
    }

    // Event Delegation for Edit Buttons
    document.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.edit-user-btn');
        if (editBtn) {
            try {
                const user = JSON.parse(editBtn.getAttribute('data-user'));
                selectUserForEdit(user);
            } catch (err) {
                console.error('Error parsing user data:', err);
            }
        }
    });

    // AJAX Live Search & Pagination Execution
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('userSearchInput');
        const clearBtn = document.getElementById('userClearBtn');
        const container = document.getElementById('usersTableContainer');
        let searchTimeout = null;

        function fetchUsersData(url) {
            container.style.opacity = '0.5';
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.html !== undefined) {
                    container.innerHTML = data.html;
                }
            })
            .catch(err => console.error('AJAX fetch error:', err))
            .finally(() => {
                container.style.opacity = '1';
            });
        }

        function triggerSearch() {
            const query = searchInput.value.trim();
            if (query !== '') {
                clearBtn.style.display = 'inline-block';
            } else {
                clearBtn.style.display = 'none';
            }
            const targetUrl = `{{ route('admin.users') }}?search=${encodeURIComponent(query)}`;
            fetchUsersData(targetUrl);
        }

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(triggerSearch, 300);
            });

            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimeout);
                    triggerSearch();
                }
            });
        }

        // AJAX Pagination Click Interceptor
        container.addEventListener('click', function(e) {
            const link = e.target.closest('.ajax-pagination-wrapper a, .pagination a');
            if (link && link.href) {
                e.preventDefault();
                fetchUsersData(link.href);
            }
        });
    });
</script>
@endsection
