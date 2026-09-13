@extends('layouts.app')

@section('title', 'Edit Blog Post - Admin Panel')
@section('page_header', 'Edit Blog Post')

@section('content')
<div style="max-width: 950px; margin: 0 auto;">

    <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('admin.blogs') }}" style="color: var(--color-primary); text-decoration: none; font-size: 0.9rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-arrow-left"></i> Back to Blog Articles
        </a>
        <a href="{{ route('blog.show', $blog->slug) }}" target="_blank" class="btn-outline" style="padding: 6px 14px; font-size: 0.82rem; text-decoration: none;">
            <i class="fa-solid fa-arrow-up-right-from-square" style="margin-right: 6px;"></i> View Live Article
        </a>
    </div>

    @if($errors->any())
        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; border-radius: var(--radius-md); padding: 14px 18px; color: #fca5a5; margin-bottom: 1.5rem;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="glass custom-card">
        <h3 class="card-title" style="margin-bottom: 1.5rem;"><i class="fa-solid fa-pen-to-square text-gradient"></i> Edit Blog Article</h3>

        <form action="{{ route('admin.blogs.update', $blog->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div class="form-group">
                <label for="title" class="form-label">Article Title <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $blog->title) }}" required>
            </div>

            <!-- Category & Status Row -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="form-group">
                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                    <input type="text" name="category" id="category" class="form-control" value="{{ old('category', $blog->category) }}" required>
                </div>
                <div class="form-group">
                    <label for="status" class="form-label">Publication Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="published" {{ old('status', $blog->status) === 'published' ? 'selected' : '' }}>Published (Live immediately)</option>
                        <option value="draft" {{ old('status', $blog->status) === 'draft' ? 'selected' : '' }}>Draft (Hidden)</option>
                    </select>
                </div>
            </div>

            <!-- Featured Image Upload & Preview -->
            <div class="form-group">
                <label for="image" class="form-label">Featured Header Image</label>
                @if($blog->image)
                    <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 15px;">
                        <img src="{{ asset($blog->image) }}" alt="Current Image" style="height: 70px; border-radius: 6px; border: 1px solid var(--border-color);">
                        <span style="font-size: 0.82rem; color: var(--text-secondary);">Current header image. Upload new file below to replace.</span>
                    </div>
                @endif
                <input type="file" name="image" id="image" class="form-control" accept="image/*">
                <small style="color: var(--text-muted); display: block; margin-top: 4px;">Recommended size: 1200x630px. Max size: 4MB (JPG, PNG, WEBP).</small>
            </div>

            <!-- Excerpt / Short Summary -->
            <div class="form-group">
                <label for="excerpt" class="form-label">Short Excerpt / Preview Summary</label>
                <textarea name="excerpt" id="excerpt" rows="2" class="form-control">{{ old('excerpt', $blog->excerpt) }}</textarea>
            </div>

            <!-- Full Article Content -->
            <div class="form-group">
                <label for="content" class="form-label">Full Article Content (HTML / Text) <span class="text-danger">*</span></label>
                <textarea name="content" id="content" rows="12" class="form-control" style="font-family: monospace; font-size: 0.9rem;" required>{{ old('content', $blog->content) }}</textarea>
            </div>

            <!-- SEO Settings Box -->
            <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 2rem;">
                <strong style="color: var(--color-primary); font-size: 0.95rem; display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <i class="fa-solid fa-magnifying-glass"></i> SEO Meta Settings (Optional)
                </strong>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="meta_title" class="form-label" style="font-size: 0.85rem;">SEO Meta Title</label>
                    <input type="text" name="meta_title" id="meta_title" class="form-control" value="{{ old('meta_title', $blog->meta_title) }}">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="meta_description" class="form-label" style="font-size: 0.85rem;">SEO Meta Description</label>
                    <textarea name="meta_description" id="meta_description" rows="2" class="form-control">{{ old('meta_description', $blog->meta_description) }}</textarea>
                </div>
            </div>

            <div style="display: flex; gap: 15px; justify-content: flex-end;">
                <a href="{{ route('admin.blogs') }}" class="btn-outline" style="padding: 12px 24px; text-decoration: none;">Cancel</a>
                <button type="submit" class="btn-gradient" style="padding: 12px 32px; font-weight: 700; font-size: 1rem;">
                    <i class="fa-solid fa-floppy-disk" style="margin-right: 8px;"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
