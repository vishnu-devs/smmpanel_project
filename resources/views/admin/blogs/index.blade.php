@extends('layouts.app')

@section('title', 'Manage Blog Articles - Admin Panel')
@section('page_header', 'Blog Management')

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">

    <!-- Top Action & Stats Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 15px;">
        <div>
            <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 4px;">Published Articles & Posts</h2>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0;">Create, edit, and manage SEO blog posts for your SMM panel</p>
        </div>
        <a href="{{ route('admin.blogs.create') }}" class="btn-gradient" style="padding: 11px 22px; font-weight: 700; border-radius: var(--radius-md); text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-plus"></i> Write New Blog Post
        </a>
    </div>

    @if(session('success'))
        <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; border-radius: var(--radius-md); padding: 12px 18px; color: #a7f3d0; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Filters & Search -->
    <div class="glass custom-card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
        <form action="{{ route('admin.blogs') }}" method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
            <div style="flex-grow: 1; min-width: 250px;">
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by title or category...">
            </div>
            <div style="min-width: 160px;">
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
            <button type="submit" class="btn-gradient" style="padding: 10px 20px;">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.blogs') }}" class="btn-outline" style="padding: 10px 16px; text-decoration: none;">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Blogs Table -->
    <div class="glass custom-card" style="padding: 0; overflow: hidden;">
        @if($blogs->count() > 0)
            <div class="table-responsive">
                <table class="custom-table" style="margin: 0; min-width: 800px;">
                    <thead>
                        <tr>
                            <th style="width: 70px;">Image</th>
                            <th>Title & Slug</th>
                            <th>Category</th>
                            <th>Views</th>
                            <th>Status</th>
                            <th>Published Date</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($blogs as $blog)
                            <tr>
                                <td>
                                    @if($blog->image)
                                        <img src="{{ asset($blog->image) }}" alt="Thumbnail" style="width: 50px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border-color);">
                                    @else
                                        <div style="width: 50px; height: 40px; border-radius: 6px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 0.9rem;">
                                            <i class="fa-solid fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 2px;">
                                        {{ $blog->title }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;">
                                        /blog/{{ $blog->slug }}
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size: 0.8rem; background: rgba(255,255,255,0.06); padding: 3px 8px; border-radius: 4px; color: var(--text-secondary);">
                                        {{ $blog->category }}
                                    </span>
                                </td>
                                <td style="font-weight: 600;">
                                    <i class="fa-regular fa-eye" style="color: var(--text-muted); margin-right: 4px;"></i> {{ number_format($blog->views) }}
                                </td>
                                <td>
                                    @if($blog->status === 'published')
                                        <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);">Published</span>
                                    @else
                                        <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);">Draft</span>
                                    @endif
                                </td>
                                <td style="font-size: 0.85rem; color: var(--text-secondary);">
                                    {{ $blog->created_at->format('M d, Y') }}
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 8px;">
                                        <a href="{{ route('blog.show', $blog->slug) }}" target="_blank" class="btn-outline" style="padding: 5px 10px; font-size: 0.8rem;" title="View Live">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                        <a href="{{ route('admin.blogs.edit', $blog->id) }}" class="btn-outline" style="padding: 5px 10px; font-size: 0.8rem;" title="Edit Post">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('admin.blogs.delete', $blog->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this blog post?');" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-outline" style="padding: 5px 10px; font-size: 0.8rem; border-color: rgba(239, 68, 68, 0.4); color: #ef4444;" title="Delete Post">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding: 1.25rem;">
                {{ $blogs->links() }}
            </div>
        @else
            <div style="text-align: center; padding: 4rem 2rem;">
                <i class="fa-solid fa-newspaper" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                <h3 style="font-size: 1.3rem; margin-bottom: 8px;">No Blog Articles Yet</h3>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Click the button below to write and publish your first article.</p>
                <a href="{{ route('admin.blogs.create') }}" class="btn-gradient" style="padding: 11px 24px; font-weight: 700; border-radius: var(--radius-md); text-decoration: none;">
                    <i class="fa-solid fa-plus" style="margin-right: 6px;"></i> Write First Blog Post
                </a>
            </div>
        @endif
    </div>

</div>
@endsection
