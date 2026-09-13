@extends('layouts.app')

@section('title', 'Feedback & Feature Suggestions - ' . App\Models\Setting::get('site_name', 'RishiSMM'))
@section('page_header', 'Feedback Center')

@section('content')
<div style="max-width: 1100px; margin: 0 auto;">

    <div class="card-grid" style="grid-template-columns: 1fr 1.3fr; align-items: start; gap: 1.5rem;">
        
        <!-- Feedback Form Card -->
        <div class="glass custom-card animate-fade-in" style="padding: 1.5rem;">
            <h3 class="card-title" style="margin-bottom: 0.5rem;">
                <i class="fa-solid fa-comment-dots text-gradient"></i> Share Your Feedback
            </h3>
            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.5rem; line-height: 1.4;">
                Facing an issue or have a feature idea? Share your feedback along with screenshots below. Your message will be directly emailed to our admin team.
            </p>

            <form action="{{ route('feedback.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Category Field -->
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="category" class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; display: block;">
                        Feedback Type <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="category" id="category" class="form-control" style="font-size: 0.9rem;" required>
                        <option value="issue" {{ old('category') == 'issue' ? 'selected' : '' }}>🐛 Report an Issue / Bug</option>
                        <option value="feature_request" {{ old('category') == 'feature_request' ? 'selected' : '' }}>💡 Request New Feature</option>
                        <option value="ui_feedback" {{ old('category') == 'ui_feedback' ? 'selected' : '' }}>🎨 UI & Usability Feedback</option>
                        <option value="payment_issue" {{ old('category') == 'payment_issue' ? 'selected' : '' }}>💳 Payment / Deposit Concern</option>
                        <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>📌 General Feedback</option>
                    </select>
                </div>

                <!-- Title / Subject Field -->
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="title" class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; display: block;">
                        Subject / Title <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="title" id="title" class="form-control"
                           placeholder="e.g. Orders delay on Instagram Views or Add UPI QR Scanner"
                           value="{{ old('title') }}" required style="font-size: 0.9rem;">
                </div>

                <!-- Details Textarea -->
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="details" class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; display: block;">
                        Detailed Description <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea name="details" id="details" class="form-control" rows="5"
                              placeholder="Please explain the issue or your feature request in detail..."
                              required style="font-size: 0.9rem; line-height: 1.5;">{{ old('details') }}</textarea>
                </div>

                <!-- Screenshot Upload -->
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; display: block;">
                        Attach Screenshot (Optional)
                    </label>
                    
                    <div id="dropZone" style="border: 2px dashed var(--border-color, rgba(255,255,255,0.15)); border-radius: var(--radius-sm); padding: 1.25rem; text-align: center; background: rgba(255,255,255,0.02); cursor: pointer; transition: all 0.2s ease;">
                        <input type="file" name="screenshot" id="screenshotInput" accept="image/jpeg,image/png,image/webp" style="display: none;">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2rem; color: var(--color-primary); margin-bottom: 8px; display: block;"></i>
                        <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-primary);">Click or Drag & Drop Screenshot</span>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">Supports JPG, PNG, WEBP (Max 5MB)</div>
                    </div>

                    <!-- Live Image Preview Container -->
                    <div id="previewWrapper" style="display: none; margin-top: 10px; position: relative;">
                        <img id="previewImage" src="" alt="Screenshot Preview" style="max-width: 100%; max-height: 200px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); object-fit: contain;">
                        <button type="button" id="removeImgBtn" style="position: absolute; top: 6px; right: 6px; background: rgba(239,68,68,0.9); color: white; border: none; border-radius: 50%; width: 26px; height: 26px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;" title="Remove image">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-gradient" style="width: 100%; padding: 12px; font-weight: 700; font-size: 0.95rem; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="fa-solid fa-paper-plane"></i> Submit & Email Admin
                </button>
            </form>
        </div>

        <!-- History & Status Card -->
        <div class="glass custom-card animate-fade-in" style="padding: 1.5rem;">
            <h3 class="card-title" style="margin-bottom: 1rem;">
                <i class="fa-solid fa-clock-rotate-left text-gradient"></i> My Submitted Feedbacks
            </h3>

            @if($feedbacks->count() > 0)
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($feedbacks as $fb)
                        <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color, rgba(255,255,255,0.08)); border-radius: var(--radius-sm); padding: 12px 14px;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 6px;">
                                <div style="font-weight: 700; font-size: 0.92rem; color: var(--text-primary);">
                                    {{ $fb->title }}
                                </div>
                                <div style="white-space: nowrap;">
                                    @if($fb->status === 'resolved')
                                        <span class="badge" style="background: rgba(16,185,129,0.15); color: #10b981; border: 1px solid rgba(16,185,129,0.3); font-size: 0.72rem; padding: 2px 8px; border-radius: 10px; font-weight: 700;">Resolved</span>
                                    @elseif($fb->status === 'reviewed')
                                        <span class="badge" style="background: rgba(59,130,246,0.15); color: #60a5fa; border: 1px solid rgba(59,130,246,0.3); font-size: 0.72rem; padding: 2px 8px; border-radius: 10px; font-weight: 700;">Reviewed</span>
                                    @else
                                        <span class="badge" style="background: rgba(245,158,11,0.15); color: #f59e0b; border: 1px solid rgba(245,158,11,0.3); font-size: 0.72rem; padding: 2px 8px; border-radius: 10px; font-weight: 700;">Pending</span>
                                    @endif
                                </div>
                            </div>

                            <div style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 8px; display: flex; gap: 12px; flex-wrap: wrap;">
                                <span><i class="fa-solid fa-tag"></i> {{ $fb->category_label }}</span>
                                <span><i class="fa-solid fa-calendar"></i> {{ $fb->created_at->format('d M Y, h:i A') }}</span>
                            </div>

                            <div style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.4; white-space: pre-wrap; word-break: break-word;">{{ $fb->details }}</div>

                            @if($fb->screenshot_path)
                                <div style="margin-top: 10px;">
                                    <a href="{{ asset($fb->screenshot_path) }}" target="_blank" style="font-size: 0.78rem; color: var(--color-info); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fa-solid fa-image"></i> View Uploaded Screenshot
                                    </a>
                                </div>
                            @endif

                            @if($fb->admin_notes)
                                <div style="margin-top: 10px; padding: 8px 12px; background: rgba(59, 130, 246, 0.08); border-left: 3px solid #3b82f6; border-radius: 4px; font-size: 0.8rem; color: var(--text-primary);">
                                    <strong style="color: #60a5fa;">Admin Response:</strong> {{ $fb->admin_notes }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div style="margin-top: 1.25rem;">
                    {{ $feedbacks->links('partials.pagination') }}
                </div>
            @else
                <div style="text-align: center; color: var(--text-muted); padding: 3rem 1rem;">
                    <i class="fa-solid fa-comment-slash" style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.4;"></i>
                    <p style="font-size: 0.9rem;">No feedback submitted yet. Use the form to submit your first feedback!</p>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('screenshotInput');
        const previewWrapper = document.getElementById('previewWrapper');
        const previewImage = document.getElementById('previewImage');
        const removeImgBtn = document.getElementById('removeImgBtn');

        if (dropZone && fileInput) {
            dropZone.addEventListener('click', () => fileInput.click());

            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropZone.style.borderColor = 'var(--color-primary)';
                dropZone.style.background = 'rgba(255,255,255,0.06)';
            });

            dropZone.addEventListener('dragleave', () => {
                dropZone.style.borderColor = 'var(--border-color, rgba(255,255,255,0.15))';
                dropZone.style.background = 'rgba(255,255,255,0.02)';
            });

            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropZone.style.borderColor = 'var(--border-color, rgba(255,255,255,0.15))';
                dropZone.style.background = 'rgba(255,255,255,0.02)';

                if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                    fileInput.files = e.dataTransfer.files;
                    handleFileSelect(e.dataTransfer.files[0]);
                }
            });

            fileInput.addEventListener('change', function () {
                if (this.files && this.files[0]) {
                    handleFileSelect(this.files[0]);
                }
            });

            function handleFileSelect(file) {
                if (!file.type.match('image.*')) {
                    alert('Please select an image file (JPG, PNG, WEBP).');
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    alert('Image size exceeds 5MB limit.');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImage.src = e.target.result;
                    previewWrapper.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }

            if (removeImgBtn) {
                removeImgBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    fileInput.value = '';
                    previewImage.src = '';
                    previewWrapper.style.display = 'none';
                });
            }
        }
    });
</script>
@endsection
