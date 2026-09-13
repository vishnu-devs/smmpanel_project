@extends('layouts.app')

@section('title', 'Manage Backups - Growinsta')
@section('page_header', 'System Backup Manager')

@section('content')
<div style="max-width: 1100px; margin: 0 auto;">

    <!-- Backup Generator Action Card -->
    <div class="glass custom-card" style="margin-bottom: 2rem;">
        <h3 class="card-title"><i class="fa-solid fa-download text-gradient"></i> Package New Backup</h3>
        <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem;">
            Create manual, offline-safe SQL database archives or compression packages of files inside your upload directories.
        </p>
        
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <!-- Database Backup -->
            <form action="{{ route('admin.backups.create') }}" method="POST" style="flex: 1; min-width: 250px;">
                @csrf
                <input type="hidden" name="type" value="db">
                <button type="submit" class="btn-gradient" style="width: 100%; padding: 14px; text-align: center; display: flex; align-items: center; justify-content: center; gap: 10px;">
                    <i class="fa-solid fa-database"></i> Backup Database (SQL)
                </button>
            </form>

            <!-- Storage Files Backup -->
            <form action="{{ route('admin.backups.create') }}" method="POST" style="flex: 1; min-width: 250px;">
                @csrf
                <input type="hidden" name="type" value="storage">
                <button type="submit" class="btn-gradient" style="width: 100%; padding: 14px; background: var(--grad-purple); text-align: center; display: flex; align-items: center; justify-content: center; gap: 10px;">
                    <i class="fa-solid fa-file-zipper"></i> Backup Storage Folders (ZIP)
                </button>
            </form>
        </div>
    </div>

    <!-- Backups History Table -->
    <div class="glass custom-card">
        <h3 class="card-title"><i class="fa-solid fa-file-medical"></i> Available Archives ({{ $backups->count() }})</h3>
        
        @if($backups->count() > 0)
            <div class="table-responsive">
                <table class="custom-table" style="font-size: 0.9rem;">
                    <thead>
                        <tr>
                            <th>Date Created</th>
                            <th>Filename</th>
                            <th>Type</th>
                            <th>File Size</th>
                            <th>Status</th>
                            <th style="text-align: right; width: 200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($backups as $b)
                            <tr>
                                <td style="font-size: 0.85rem; color: var(--text-secondary);">
                                    {{ $b->created_at }}
                                </td>
                                <td style="font-family: monospace; font-size: 0.85rem; color: var(--text-primary);">
                                    {{ $b->filename }}
                                </td>
                                <td>
                                    @if($b->type === 'db')
                                        <span class="badge" style="background: rgba(59,130,246,0.1); color: #3b82f6;">Database</span>
                                    @else
                                        <span class="badge" style="background: rgba(168,85,247,0.1); color: #a855f7;">Files Zip</span>
                                    @endif
                                </td>
                                <td>
                                    {{ number_format($b->size_bytes / (1024 * 1024), 2) }} MB
                                </td>
                                <td>
                                    @if($b->status === 'completed')
                                        <span class="badge badge-completed">Ready</span>
                                    @else
                                        <span class="badge badge-canceled" title="{{ $b->error_message }}">Failed</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                        @if($b->status === 'completed')
                                            <a href="{{ route('admin.backups.download', $b->id) }}" class="btn-outline" style="padding: 6px 12px; font-size: 0.75rem; border-radius: var(--radius-sm); text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                                <i class="fa-solid fa-cloud-arrow-down"></i> Download
                                            </a>
                                        @endif
                                        <form action="{{ route('admin.backups.delete', $b->id) }}" method="POST" onsubmit="return confirm('Permanently delete this backup file from server disk?')">
                                            @csrf
                                            <button type="submit" class="btn-gradient" style="padding: 6px 12px; font-size: 0.75rem; border-radius: var(--radius-sm); background: var(--grad-danger);">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 1.5rem; display: flex; justify-content: center;">
                {{ $backups->links() }}
            </div>
        @else
            <div style="text-align: center; color: var(--text-muted); padding: 4rem;">
                No manual backup archives compiled yet. Choose an option above to generate one!
            </div>
        @endif
    </div>

</div>
@endsection
