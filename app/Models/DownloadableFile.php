<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DownloadableFile extends Model
{
    protected $fillable = [
        'name',
        'filename',
        'file_path',
        'file_type',
        'file_size',
        'description',
        'product_id',
        'is_active',
        'download_limit',
        'download_count',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the product that owns this file.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if file exists on storage.
     */
    public function fileExists(): bool
    {
        return Storage::exists($this->file_path);
    }

    /**
     * Get file URL for download.
     */
    public function getDownloadUrl(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get human readable file size.
     */
    public function getHumanFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if download limit is reached.
     */
    public function isDownloadLimitReached(): bool
    {
        if ($this->download_limit === null) {
            return false; // unlimited downloads
        }
        
        return $this->download_count >= $this->download_limit;
    }

    /**
     * Increment download count.
     */
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    /**
     * Get active files only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Filter by file type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('file_type', $type);
    }

    /**
     * Get files that haven't reached download limit.
     */
    public function scopeAvailableForDownload($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('download_limit')
                          ->orWhereRaw('download_count < download_limit');
                    });
    }
}
