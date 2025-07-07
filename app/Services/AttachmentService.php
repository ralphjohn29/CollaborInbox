<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Webklex\PHPIMAP\Message;
use App\Services\TenantContext;

class AttachmentService
{
    /**
     * Extract and save attachments from an email message
     *
     * @param Message $message The IMAP message containing attachments
     * @param string $emailId Unique identifier for the email (e.g., UUID or message ID)
     * @return array Array of saved attachment information
     */
    public function processAttachments(Message $message, string $emailId): array
    {
        if (!$message->hasAttachments()) {
            return [];
        }

        $savedAttachments = [];
        $attachments = $message->getAttachments();

        foreach ($attachments as $attachment) {
            try {
                // Skip inline attachments that are likely to be part of the email body (e.g. email signatures)
                if ($this->isInlineImage($attachment) && $attachment->getSize() < 50000) {
                    continue;
                }

                $filename = $this->sanitizeFilename($attachment->getName());
                $contentId = $attachment->getContentId();
                $contentType = $attachment->getContentType();
                $size = $attachment->getSize();
                
                // Check file size limit (10MB)
                if ($size > 10 * 1024 * 1024) {
                    Log::warning("Attachment too large, skipping", [
                        'filename' => $filename,
                        'size' => $size,
                        'email_id' => $emailId
                    ]);
                    continue;
                }

                // Generate unique filename to prevent collisions
                $uniqueFilename = $this->generateUniqueFilename($filename, $emailId);
                
                // Save the attachment to tenant-specific storage
                $path = $this->saveAttachment($attachment, $uniqueFilename, $emailId);
                
                $savedAttachments[] = [
                    'original_filename' => $filename,
                    'stored_filename' => $uniqueFilename,
                    'path' => $path,
                    'content_id' => $contentId,
                    'content_type' => $contentType,
                    'size' => $size,
                    'is_inline' => $this->isInlineImage($attachment),
                    'can_preview' => $this->canGeneratePreview($contentType)
                ];
            } catch (\Exception $e) {
                Log::error("Failed to process attachment", [
                    'error' => $e->getMessage(),
                    'filename' => $attachment->getName() ?? 'unknown',
                    'email_id' => $emailId
                ]);
            }
        }

        return $savedAttachments;
    }

    /**
     * Save an attachment file to tenant-specific storage
     *
     * @param \Webklex\PHPIMAP\Attachment $attachment
     * @param string $filename
     * @param string $emailId
     * @return string The storage path
     */
    protected function saveAttachment($attachment, string $filename, string $emailId): string
    {
        // Get current tenant ID for isolated storage
        $tenantId = $this->getTenantId();
        
        // Generate a path specific to this tenant and email
        $path = "tenants/{$tenantId}/attachments/{$emailId}";
        
        // Save the attachment content to the storage
        Storage::put("{$path}/{$filename}", $attachment->getContent());
        
        return "{$path}/{$filename}";
    }

    /**
     * Get the current tenant ID for storage isolation
     *
     * @return string|int
     */
    protected function getTenantId()
    {
        // Assuming TenantContext is properly set up in middleware
        if (app()->has(TenantContext::class) && app(TenantContext::class)->hasTenant()) {
            return app(TenantContext::class)->getTenantId();
        }
        
        // Default tenant ID for system-level operations or testing
        return 'system';
    }

    /**
     * Sanitize filename to prevent directory traversal and other issues
     *
     * @param string $filename
     * @return string
     */
    protected function sanitizeFilename(string $filename): string
    {
        // Remove directory paths
        $filename = basename($filename);
        
        // Replace problematic characters
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
        
        // Ensure the filename is not too long
        if (strlen($filename) > 255) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $basename = pathinfo($filename, PATHINFO_FILENAME);
            $basename = substr($basename, 0, 245 - strlen($extension));
            $filename = $basename . '.' . $extension;
        }
        
        return $filename;
    }

    /**
     * Generate a unique filename to prevent collisions
     *
     * @param string $filename
     * @param string $emailId
     * @return string
     */
    protected function generateUniqueFilename(string $filename, string $emailId): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        $uniqueId = substr(md5($emailId . $filename . time()), 0, 8);
        
        return "{$basename}_{$uniqueId}.{$extension}";
    }

    /**
     * Check if the attachment is an inline image
     *
     * @param \Webklex\PHPIMAP\Attachment $attachment
     * @return bool
     */
    protected function isInlineImage($attachment): bool
    {
        $contentId = $attachment->getContentId();
        $contentDisposition = $attachment->getContentDisposition();
        $contentType = $attachment->getContentType();
        
        return !empty($contentId) &&
               (strpos($contentType, 'image/') === 0 || 
                strpos($contentDisposition, 'inline') !== false);
    }

    /**
     * Determine if we can generate a preview for this file type
     *
     * @param string $contentType
     * @return bool
     */
    protected function canGeneratePreview(string $contentType): bool
    {
        $previewableTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/svg+xml',
            'application/pdf',
            'text/plain',
            'text/html',
            'text/css',
            'text/javascript',
            'application/json',
            'application/xml'
        ];
        
        return in_array($contentType, $previewableTypes);
    }

    /**
     * Generate a preview image/thumbnail for supported file types
     *
     * @param string $path Storage path to the file
     * @param string $contentType MIME type of the file
     * @return string|null Path to the preview file, or null if preview cannot be generated
     */
    public function generatePreview(string $path, string $contentType): ?string
    {
        // Basic implementation - just return the original for images
        // In a real implementation, you'd use libraries like Imagick or a PDF renderer
        if (strpos($contentType, 'image/') === 0) {
            return $path; // For images, we can use the original
        }
        
        // For text-based files, we might render them to an image
        if (strpos($contentType, 'text/') === 0 || 
            $contentType === 'application/json' || 
            $contentType === 'application/xml') {
            
            // In a real implementation, render a preview image
            // For now, we'll just return null
            return null;
        }
        
        // For PDFs, we might render the first page
        if ($contentType === 'application/pdf') {
            // In a real implementation, render the first page
            // For now, we'll just return null
            return null;
        }
        
        return null;
    }

    /**
     * Get a public URL for an attachment
     *
     * @param string $path
     * @param int $expiresInMinutes
     * @return string
     */
    public function getAttachmentUrl(string $path, int $expiresInMinutes = 60): string
    {
        // Generate a temporary signed URL
        return Storage::temporaryUrl($path, now()->addMinutes($expiresInMinutes));
    }
} 