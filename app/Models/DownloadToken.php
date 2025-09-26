<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DownloadToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'grant_id',
        'token',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($token) {
            if (empty($token->token)) {
                $token->token = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the download grant that owns the token.
     */
    public function grant(): BelongsTo
    {
        return $this->belongsTo(DownloadGrant::class, 'grant_id');
    }

    /**
     * Alias for grant relationship.
     */
    public function downloadGrant(): BelongsTo
    {
        return $this->grant();
    }

    /**
     * Check if the token is still valid.
     */
    public function isValid(): bool
    {
        if ($this->used_at) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Mark the token as used.
     */
    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    /**
     * Get the minutes until the token expires.
     */
    public function minutesUntilExpiry(): int
    {
        if (!$this->expires_at) {
            return PHP_INT_MAX;
        }

        $minutes = now()->diffInMinutes($this->expires_at, false);
        return max(0, $minutes);
    }

    /**
     * Check if the token is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the token has been used.
     */
    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }

    /**
     * Scope a query to only include expired tokens.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope a query to only include tokens for a specific grant.
     */
    public function scopeForGrant($query, $grantId)
    {
        return $query->where('grant_id', $grantId);
    }

    /**
     * Scope a query to only include unused tokens.
     */
    public function scopeUnused($query)
    {
        return $query->whereNull('used_at');
    }

    /**
     * Scope a query to only include valid tokens.
     */
    public function scopeValid($query)
    {
        return $query->whereNull('used_at')
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }
}
