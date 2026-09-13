<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Feedback;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FeedbackController extends Controller
{
    /**
     * Display feedback submission page with user's history
     */
    public function index()
    {
        $feedbacks = Feedback::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('user.feedback', compact('feedbacks'));
    }

    /**
     * Store new feedback and notify admin via email
     */
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|in:issue,feature_request,ui_feedback,payment_issue,other',
            'title' => 'required|string|max:200',
            'details' => 'required|string|min:10|max:5000',
            'screenshot' => 'nullable|file|image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'category.required' => 'Please select a feedback category.',
            'title.required' => 'Please provide a short subject or title for your feedback.',
            'details.required' => 'Please describe your feedback or problem in detail.',
            'details.min' => 'Please provide at least 10 characters in details.',
            'screenshot.image' => 'The uploaded screenshot must be a valid image (JPG, PNG, WEBP).',
            'screenshot.max' => 'Screenshot size must not exceed 5MB.',
        ]);

        $user = Auth::user();
        $screenshotPath = null;

        // Secure Screenshot Upload
        if ($request->hasFile('screenshot') && $request->file('screenshot')->isValid()) {
            try {
                $screenshotPath = \App\Services\SecureUploadService::saveImage($request->file('screenshot'), 'feedback');
            } catch (\InvalidArgumentException $e) {
                return back()->with('error', $e->getMessage())->withInput();
            }
        }

        // Create Feedback DB Record
        $feedback = Feedback::create([
            'user_id' => $user->id,
            'category' => $request->category,
            'title' => $request->title,
            'details' => $request->details,
            'screenshot_path' => $screenshotPath,
            'status' => 'pending',
        ]);

        // Audit Log
        ActivityLog::log('feedback_submitted', [
            'feedback_id' => $feedback->id,
            'category' => $request->category,
            'has_screenshot' => !empty($screenshotPath),
        ]);

        // Email Notification to Admin
        $this->notifyAdmin($feedback, $user);

        return back()->with('success', 'Thank you! Your feedback has been submitted successfully and emailed to our team.');
    }

    /**
     * Dispatch email notification to admin about new feedback
     */
    private function notifyAdmin(Feedback $feedback, User $user): void
    {
        try {
            // Resolve admin email
            $adminEmail = Setting::get('admin_email');
            if (!$adminEmail) {
                $adminUser = User::where('role', 'admin')->first();
                $adminEmail = $adminUser ? $adminUser->email : Setting::get('support_email', 'admin@' . request()->getHost());
            }

            $siteName = Setting::get('site_name', 'RishiSMM');
            $categoryLabel = $feedback->category_label;
            $subject = "[{$siteName} Feedback] {$categoryLabel}: " . Str::limit($feedback->title, 50);
            $emailTitle = "New Customer Feedback Received";

            $screenshotHtml = '';
            if ($feedback->screenshot_path) {
                $fullUrl = asset($feedback->screenshot_path);
                $screenshotHtml = "
                    <div style='margin-top: 15px; padding: 12px; background: rgba(0,0,0,0.03); border: 1px solid #e2e8f0; border-radius: 8px;'>
                        <strong>Attached Screenshot:</strong><br>
                        <a href='{$fullUrl}' target='_blank' style='color: #2563eb; text-decoration: underline; font-weight: bold;'>View Uploaded Screenshot</a><br>
                        <img src='{$fullUrl}' alt='Feedback Screenshot' style='max-width: 100%; max-height: 400px; margin-top: 10px; border-radius: 6px; border: 1px solid #cbd5e1;'>
                    </div>
                ";
            }

            $messageBody = "
                <p>Hello Admin,</p>
                <p>A new customer feedback / issue report has been submitted on <strong>{$siteName}</strong>.</p>
                
                <table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>
                    <tr>
                        <td style='padding: 8px; font-weight: bold; width: 140px; border-bottom: 1px solid #e2e8f0;'>Customer Name:</td>
                        <td style='padding: 8px; border-bottom: 1px solid #e2e8f0;'>" . htmlspecialchars($user->name) . " (User ID: #{$user->id})</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px; font-weight: bold; border-bottom: 1px solid #e2e8f0;'>Customer Email:</td>
                        <td style='padding: 8px; border-bottom: 1px solid #e2e8f0;'>" . htmlspecialchars($user->email) . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px; font-weight: bold; border-bottom: 1px solid #e2e8f0;'>Category:</td>
                        <td style='padding: 8px; border-bottom: 1px solid #e2e8f0;'><strong>{$categoryLabel}</strong></td>
                    </tr>
                    <tr>
                        <td style='padding: 8px; font-weight: bold; border-bottom: 1px solid #e2e8f0;'>Subject / Title:</td>
                        <td style='padding: 8px; border-bottom: 1px solid #e2e8f0;'>" . htmlspecialchars($feedback->title) . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px; font-weight: bold; border-bottom: 1px solid #e2e8f0;'>Submitted At:</td>
                        <td style='padding: 8px; border-bottom: 1px solid #e2e8f0;'>" . $feedback->created_at->format('d M Y, h:i A') . "</td>
                    </tr>
                </table>

                <div style='background: #f8fafc; padding: 15px; border-left: 4px solid #3b82f6; border-radius: 4px; margin-top: 10px;'>
                    <strong style='color: #1e293b;'>Details / Message:</strong><br>
                    <p style='white-space: pre-wrap; margin-top: 5px; color: #334155;'>" . nl2br(htmlspecialchars($feedback->details)) . "</p>
                </div>

                {$screenshotHtml}

                <p style='margin-top: 20px; font-size: 0.85rem; color: #64748b;'>
                    You can manage customer feedback submissions directly from your Admin Dashboard.
                </p>
            ";

            Setting::sendEmail($adminEmail, $subject, $emailTitle, $messageBody);
        } catch (\Throwable $e) {
            Log::error("Failed sending admin feedback notification email: " . $e->getMessage());
        }
    }
}
