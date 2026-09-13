@extends('layouts.app')

@section('title', 'Write New Blog Post - Admin Panel')
@section('page_header', 'Create Blog Post')

@section('content')
<div style="max-width: 950px; margin: 0 auto;">

    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('admin.blogs') }}" style="color: var(--color-primary); text-decoration: none; font-size: 0.9rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-arrow-left"></i> Back to Blog Articles
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
        <h3 class="card-title" style="margin-bottom: 1.5rem;"><i class="fa-solid fa-pen-nib text-gradient"></i> Write New Blog Article</h3>

        <form action="{{ route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Title -->
            <div class="form-group">
                <label for="title" class="form-label">Article Title <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" placeholder="e.g. 7 Proven Ways to Get 10K Real Instagram Followers in 2026" required>
            </div>

            <!-- Category & Status Row -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="form-group">
                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                    <input type="text" name="category" id="category" class="form-control" value="{{ old('category', 'Instagram Growth') }}" placeholder="e.g. Instagram Growth, YouTube, SMM Reseller" required>
                </div>
                <div class="form-group">
                    <label for="status" class="form-label">Publication Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published (Live immediately)</option>
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft (Hidden)</option>
                    </select>
                </div>
            </div>

            <!-- Featured Image Upload -->
            <div class="form-group">
                <label for="image" class="form-label">Featured Header Image (Optional)</label>
                <input type="file" name="image" id="image" class="form-control" accept="image/*">
                <small style="color: var(--text-muted); display: block; margin-top: 4px;">Recommended size: 1200x630px. Max size: 4MB (JPG, PNG, WEBP).</small>
            </div>

            <!-- Excerpt / Short Summary -->
            <div class="form-group">
                <label for="excerpt" class="form-label">Short Excerpt / Preview Summary</label>
                <textarea name="excerpt" id="excerpt" rows="2" class="form-control" placeholder="Short description displayed on blog card previews...">{{ old('excerpt') }}</textarea>
            </div>

            <!-- Full Article Content -->
            <div class="form-group">
                <label for="content" class="form-label">Full Article Content (HTML / Text) <span class="text-danger">*</span></label>
                <textarea name="content" id="content" rows="12" class="form-control" placeholder="Write full article here. You can use standard HTML like <h2>, <p>, <ul>, <li>, <blockquote>, <strong>..." style="font-family: monospace; font-size: 0.9rem;" required>{{ old('content') }}</textarea>
            </div>

            <!-- SEO Settings Accordion / Box -->
            <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 2rem;">
                <strong style="color: var(--color-primary); font-size: 0.95rem; display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <i class="fa-solid fa-magnifying-glass"></i> SEO Meta Settings (Optional)
                </strong>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="meta_title" class="form-label" style="font-size: 0.85rem;">SEO Meta Title</label>
                    <input type="text" name="meta_title" id="meta_title" class="form-control" value="{{ old('meta_title') }}" placeholder="Leave blank to use Article Title">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="meta_description" class="form-label" style="font-size: 0.85rem;">SEO Meta Description</label>
                    <textarea name="meta_description" id="meta_description" rows="2" class="form-control" placeholder="Leave blank to auto-generate from excerpt...">{{ old('meta_description') }}</textarea>
                </div>
            </div>

            <div style="display: flex; gap: 15px; justify-content: flex-end;">
                <a href="{{ route('admin.blogs') }}" class="btn-outline" style="padding: 12px 24px; text-decoration: none;">Cancel</a>
                <button type="submit" class="btn-gradient" style="padding: 12px 32px; font-weight: 700; font-size: 1rem;">
                    <i class="fa-solid fa-cloud-arrow-up" style="margin-right: 8px;"></i> Publish Article
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
