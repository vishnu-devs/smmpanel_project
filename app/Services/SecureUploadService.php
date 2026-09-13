<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class SecureUploadService
{
    /**
     * Dangerous extensions that must never appear anywhere in the uploaded filename
     */
    private const DANGEROUS_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phar', 'inc',
        'sh', 'bash', 'cgi', 'pl', 'py', 'exe', 'asp', 'aspx', 'jsp',
        'htaccess', 'htpasswd', 'cmd', 'bat', 'vbs', 'scr', 'dll'
    ];

    /**
     * Allowed MIME types for images
     */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'image/x-icon',
        'image/vnd.microsoft.icon',
        'image/svg+xml',
    ];

    /**
     * Validate and securely store an uploaded image file.
     * Throws \InvalidArgumentException on any security violation.
     */
    public static function saveImage(UploadedFile $file, string $subfolder = 'general'): string
    {
        if (!$file->isValid()) {
            throw new \InvalidArgumentException('Uploaded file is invalid or corrupted.');
        }

        $originalName = strtolower($file->getClientOriginalName());

        // 1. Double Extension & Dangerous Filename Check:
        // Reject if filename contains dangerous extensions anywhere (e.g. script.php.png or shell.phtml.jpg)
        foreach (self::DANGEROUS_EXTENSIONS as $dangerExt) {
            if (preg_match('/\.' . preg_quote($dangerExt, '/') . '($|\.)/i', $originalName)) {
                throw new \InvalidArgumentException("Security Alert: Malicious or double-extension filename detected ('{$file->getClientOriginalName()}'). Upload rejected.");
            }
        }

        // 2. Null Byte Infection Check
        if (str_contains($originalName, "\0") || str_contains($originalName, '%00')) {
            throw new \InvalidArgumentException('Security Alert: Malicious null-byte detected in filename. Upload rejected.');
        }

        // 3. MIME Type Verification (from actual file content, not client header)
        $mimeType = strtolower($file->getMimeType());
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new \InvalidArgumentException("Invalid file MIME type ('{$mimeType}'). Only JPG, PNG, WEBP, GIF, ICO, and SVG images are allowed.");
        }

        // 4. Binary Image Validation (getimagesize check for raster images)
        if ($mimeType !== 'image/svg+xml') {
            $imageInfo = @getimagesize($file->getPathname());
            if ($imageInfo === false || !in_array($imageInfo[2], [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_BMP, IMAGETYPE_ICO], true)) {
                throw new \InvalidArgumentException('Security Alert: File failed binary image header validation. Upload rejected.');
            }
        }

        // 5. Safe Extension Assignment based on verified MIME type
        $ext = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/svg+xml' => 'svg',
            'image/x-icon', 'image/vnd.microsoft.icon' => 'ico',
            default => 'png',
        };

        // 6. Generate Clean Cryptographic Random Filename (Completely discards original user filename)
        $cleanFilename = Str::slug($subfolder) . '_' . time() . '_' . Str::random(24) . '.' . $ext;

        // 7. Ensure Upload Directory Exists & Secure with .htaccess
        $destinationDir = public_path('uploads/' . trim($subfolder, '/'));
        if (!file_exists($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        // Auto-create/enforce .htaccess protection inside public/uploads
        self::ensureUploadsDirectoryProtection();

        // 8. Move file securely
        $file->move($destinationDir, $cleanFilename);

        return 'uploads/' . trim($subfolder, '/') . '/' . $cleanFilename;
    }

    /**
     * Enforce .htaccess security inside public/uploads to prevent script execution
     */
    public static function ensureUploadsDirectoryProtection(): void
    {
        $baseUploadsDir = public_path('uploads');
        if (!file_exists($baseUploadsDir)) {
            mkdir($baseUploadsDir, 0755, true);
        }

        $htaccessPath = $baseUploadsDir . '/.htaccess';
        $htaccessContent = <<<HTACCESS
# Block PHP Execution inside Uploads Directory
<IfModule mod_php7.c>
    php_flag engine off
</IfModule>
<IfModule mod_php.c>
    php_flag engine off
</IfModule>
<IfModule mod_php5.c>
    php_flag engine off
</IfModule>

# Deny execution of scripts
<FilesMatch "\.(php|php3|php4|php5|php7|phtml|phar|inc|sh|cgi|pl|py|asp|aspx|exe|htaccess)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Remove handlers
RemoveHandler .php .phtml .php3 .php4 .php5 .php7 .phar .inc .sh .cgi
RemoveType .php .phtml .php3 .php4 .php5 .php7 .phar .inc .sh .cgi

<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
</IfModule>
HTACCESS;

        if (!file_exists($htaccessPath) || file_get_contents($htaccessPath) !== $htaccessContent) {
            @file_put_contents($htaccessPath, $htaccessContent);
        }
    }
}
