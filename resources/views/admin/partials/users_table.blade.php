@if($users->count() > 0)
    <!-- Desktop Table View (visible on > 768px) -->
    <div class="table-responsive desktop-only-table">
        <table class="custom-table" style="font-size: 0.9rem;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name / Email</th>
                    <th>WhatsApp</th>
                    <th>Role</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th style="text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td style="font-weight: bold; color: var(--text-muted);">#{{ $user->id }}</td>
                        <td>
                            <div style="font-weight: 600;">{{ $user->name }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-secondary);">{{ $user->email }}</div>
                        </td>
                        <td style="font-size: 0.85rem;">{{ $user->whatsapp ?? 'None' }}</td>
                        <td>
                            @if($user->isAdmin())
                                <span style="color: var(--color-primary); font-weight: 700; font-size: 0.8rem; text-transform: uppercase;">Admin</span>
                            @else
                                <span style="color: var(--text-secondary); font-size: 0.8rem;">Client</span>
                            @endif
                        </td>
                        <td style="font-weight: 700;" class="text-gradient">
                            ₹{{ number_format($user->balance, 2) }}
                        </td>
                        <td>
                            @if($user->status === 'active')
                                <span class="badge badge-completed">Active</span>
                            @else
                                <span class="badge badge-canceled">Suspended</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 5px; justify-content: center;">
                                <button type="button" class="btn-outline edit-user-btn" data-user="{{ json_encode($user) }}" style="padding: 5px 10px; font-size: 0.75rem; border-radius: var(--radius-sm);">
                                    Edit
                                </button>
                                @if(auth()->id() !== $user->id)
                                    <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete user \'{{ addslashes($user->name) }}\'?')" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn-outline" style="padding: 5px 10px; font-size: 0.75rem; border-radius: var(--radius-sm); border-color: var(--color-danger); color: var(--color-danger);">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Mobile Compact User Cards (visible on <= 768px) -->
    <div class="mobile-only-users">
        @foreach($users as $user)
            <div class="user-mobile-card glass" style="padding: 10px 12px; border-radius: var(--radius-md); border: 1px solid var(--border-color); background: rgba(255,255,255,0.02);">
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <!-- Left Info: ID, Name, Role, Email & WhatsApp -->
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                            <span style="font-size: 0.72rem; font-weight: bold; color: var(--text-muted);">#{{ $user->id }}</span>
                            <strong style="font-size: 0.88rem; color: var(--text-primary); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">{{ $user->name }}</strong>
                            @if($user->isAdmin())
                                <span class="badge" style="font-size: 0.65rem; padding: 1px 6px; background: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3);">Admin</span>
                            @else
                                <span style="font-size: 0.68rem; color: var(--text-muted);">Client</span>
                            @endif
                        </div>
                        <div style="font-size: 0.72rem; color: var(--text-secondary); text-overflow: ellipsis; overflow: hidden; white-space: nowrap; margin-top: 2px;">
                            <i class="fa-regular fa-envelope" style="font-size: 0.68rem;"></i> {{ $user->email }}
                            @if($user->whatsapp)
                                <span style="margin-left: 6px; color: #22c55e;"><i class="fa-brands fa-whatsapp"></i> {{ $user->whatsapp }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Center Info: Balance & Status Badge -->
                    <div style="text-align: right; flex-shrink: 0;">
                        <div style="font-size: 0.88rem; font-weight: 800; color: var(--color-info);" class="text-gradient">
                            ₹{{ number_format($user->balance, 2) }}
                        </div>
                        <div style="margin-top: 1px;">
                            @if($user->status === 'active')
                                <span class="badge badge-completed" style="font-size: 0.65rem; padding: 2px 6px;">Active</span>
                            @else
                                <span class="badge badge-canceled" style="font-size: 0.65rem; padding: 2px 6px;">Banned</span>
                            @endif
                        </div>
                    </div>

                    <!-- Right Actions: Edit & Delete -->
                    <div style="display: flex; gap: 4px; align-items: center; flex-shrink: 0; margin-left: 4px;">
                        <button type="button" class="btn-outline edit-user-btn" data-user="{{ json_encode($user) }}" style="padding: 5px 8px; font-size: 0.72rem; border-radius: var(--radius-sm);" title="Edit User">
                            <i class="fa-solid fa-pen-to-square"></i> Edit
                        </button>
                        @if(auth()->id() !== $user->id)
                            <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" onsubmit="return confirm('Delete user \'{{ addslashes($user->name) }}\'?')" style="margin: 0;">
                                @csrf
                                <button type="submit" class="btn-outline" style="padding: 5px 8px; font-size: 0.72rem; border-radius: var(--radius-sm); border-color: var(--color-danger); color: var(--color-danger);" title="Delete User">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="ajax-pagination-wrapper" style="margin-top: 1.5rem; display: flex; justify-content: center;">
        {{ $users->links() }}
    </div>
@else
    <div style="text-align: center; color: var(--text-muted); padding: 3rem;">
        <i class="fa-solid fa-user-slash" style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.4;"></i>
        <p>No users matching the search query found.</p>
    </div>
@endif
