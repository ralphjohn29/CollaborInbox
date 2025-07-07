<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTenancy;
use App\Services\AttachmentService;

class Attachment extends Model
{
    use HasFactory, HasTenancy;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'message_id',
        'original_filename',
        'stored_filename',
        'path',
        'content_id',
        'content_type',
        'size',
        'is_inline',
        'can_preview',
        'preview_path'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_inline' => 'boolean',
        'can_preview' => 'boolean',
        'size' => 'integer',
    ];

    /**
     * Get the message that owns the attachment.
     */
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get a temporary URL for accessing the attachment.
     *
     * @param int $expiresInMinutes
     * @return string
     */
    public function getUrl(int $expiresInMinutes = 60): string
    {
        return app(AttachmentService::class)->getAttachmentUrl($this->path, $expiresInMinutes);
    }

    /**
     * Get a preview URL for the attachment if available.
     *
     * @param int $expiresInMinutes
     * @return string|null
     */
    public function getPreviewUrl(int $expiresInMinutes = 60): ?string
    {
        if (!$this->can_preview || !$this->preview_path) {
            return null;
        }
        
        return app(AttachmentService::class)->getAttachmentUrl($this->preview_path, $expiresInMinutes);
    }

    /**
     * Determine if the attachment is an image.
     *
     * @return bool
     */
    public function isImage(): bool
    {
        return strpos($this->content_type, 'image/') === 0;
    }

    /**
     * Determine if the attachment is a document.
     *
     * @return bool
     */
    public function isDocument(): bool
    {
        $documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/csv'
        ];
        
        return in_array($this->content_type, $documentTypes);
    }

    /**
     * Get a human-readable file size.
     *
     * @return string
     */
    public function getHumanReadableSize(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
} 