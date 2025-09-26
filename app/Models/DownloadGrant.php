<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DownloadGrant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'file_id',
        'order_id',
        'max_downloads',
        'downloads_used',
        'expires_at',
    ];

    protected $casts = [
        'max_downloads' => 'integer',
        'downloads_used' => 'integer',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user that owns the download grant.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product that belongs to the download grant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the file that belongs to the download grant.
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the order that belongs to the download grant.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the download tokens for this grant.
     */
    public function downloadTokens(): HasMany
    {
        return $this->hasMany(DownloadToken::class, 'grant_id');
    }

    /**
     * Check if the grant is still valid.
     */
    public function isValid(): bool
    {
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_downloads && $this->downloads_used >= $this->max_downloads) {
            return false;
        }

        return true;
    }

    /**
     * Get the days until the grant expires.
     */
    public function daysUntilExpiry(): int
    {
        if (!$this->expires_at) {
            return PHP_INT_MAX;
        }

        $days = now()->startOfDay()->diffInDays($this->expires_at->startOfDay(), false);
        return max(0, $days);
    }

    /**
     * Increment the downloads used count.
     */
    public function incrementDownloadsUsed(): void
    {
        $this->increment('downloads_used');
    }

    /**
     * Get the number of downloads remaining.
     */
    public function downloadsRemaining(): int
    {
        if (!$this->max_downloads) {
            return PHP_INT_MAX;
        }

        return max(0, $this->max_downloads - $this->downloads_used);
    }

    /**
     * Scope a query to only include grants for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include grants for a specific product.
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to only include valid grants.
     */
    public function scopeValid($query)
    {
        return $query->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    })
                    ->whereRaw('downloads_used < max_downloads OR max_downloads IS NULL');
    }
}
