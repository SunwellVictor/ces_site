<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'slug',
        'title',
        'description',
        'price_cents',
        'currency',
        'is_active',
        'is_digital',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'price_cents' => 'integer',
        'is_active' => 'boolean',
        'is_digital' => 'boolean',
    ];

    /**
     * Get the order items for this product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the files associated with this product.
     */
    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'product_files')
                    ->withPivot('note')
                    ->withTimestamps();
    }

    /**
     * Get the download grants for this product.
     */
    public function downloadGrants(): HasMany
    {
        return $this->hasMany(DownloadGrant::class);
    }

    /**
     * Get the price in dollars.
     */
    public function getPriceAttribute(): float
    {
        return $this->price_cents / 100;
    }

    /**
     * Set the price in dollars.
     */
    public function setPriceAttribute(float $value): void
    {
        $this->attributes['price_cents'] = (int) ($value * 100);
    }

    /**
     * Get the total file size for this product.
     */
    public function getTotalFileSizeAttribute(): int
    {
        return $this->files()->sum('size_bytes');
    }

    /**
     * Get the file count for this product.
     */
    public function getFileCountAttribute(): int
    {
        return $this->files()->count();
    }

    /**
     * Check if this is a digital product.
     */
    public function getIsDigitalAttribute(): bool
    {
        return $this->attributes['is_digital'];
    }

    /**
     * Check if this product is active.
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->attributes['is_active'];
    }

    /**
     * Get active products only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get digital products only.
     */
    public function scopeDigital($query)
    {
        return $query->where('is_digital', true);
    }

    /**
     * Get physical products only.
     */
    public function scopePhysical($query)
    {
        return $query->where('is_digital', false);
    }

    /**
     * Filter products by type.
     */
    public function scopeByType($query, $type)
    {
        if ($type === 'digital') {
            return $query->where('is_digital', true);
        } elseif ($type === 'physical') {
            return $query->where('is_digital', false);
        }
        
        return $query;
    }

    /**
     * Filter products by price range.
     */
    public function scopePriceRange($query, $min = null, $max = null)
    {
        if ($min !== null) {
            $query->where('price_cents', '>=', $min);
        }
        
        if ($max !== null) {
            $query->where('price_cents', '<=', $max);
        }
        
        return $query;
    }

    /**
     * Search products by title and description.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * Get the formatted price with currency symbol.
     */
    public function getFormattedPriceAttribute(): string
    {
        $symbol = $this->currency === 'JPY' ? '¥' : '$';
        return $symbol . number_format($this->price, 2);
    }

    /**
     * Automatically generate slug when creating.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $baseSlug = Str::slug($product->title);
                $slug = $baseSlug;
                $counter = 1;
                
                // Ensure slug uniqueness
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                
                $product->slug = $slug;
            }
        });
    }
}
