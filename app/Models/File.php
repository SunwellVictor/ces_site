<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class File extends Model
{
    use HasFactory;
    protected $fillable = [
        'disk',
        'path',
        'original_name',
        'size_bytes',
        'checksum',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
    ];

    /**
     * Get the products that use this file.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_files')
                    ->withPivot('note')
                    ->withTimestamps();
    }

    /**
     * Get the download grants for this file.
     */
    public function downloadGrants(): HasMany
    {
        return $this->hasMany(DownloadGrant::class);
    }
}
