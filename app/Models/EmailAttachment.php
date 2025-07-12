<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmailAttachment extends Model
{
    protected $fillable = [
        'email_id',
        'filename',
        'mime_type',
        'size',
        'content_id',
        'storage_path',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }

    public function getUrl()
    {
        return Storage::url($this->storage_path);
    }

    public function getDownloadUrl()
    {
        return route('inbox.attachment.download', ['attachment' => $this->id]);
    }

    public function getSizeFormatted()
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->size;
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isImage()
    {
        return strpos($this->mime_type, 'image/') === 0;
    }

    public function isPdf()
    {
        return $this->mime_type === 'application/pdf';
    }

    public function isDocument()
    {
        $documentTypes = [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/csv'
        ];
        
        return in_array($this->mime_type, $documentTypes);
    }
}
