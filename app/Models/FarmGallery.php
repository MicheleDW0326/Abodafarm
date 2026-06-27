<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmGallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_profile_id',
        'image',
        'caption',
        'sort_order',
    ];

    public function farmProfile(): BelongsTo
    {
        return $this->belongsTo(FarmProfile::class);
    }

    public function getUrlAttribute(): string
    {
        return $this->image ? asset('storage/' . $this->image) : '';
    }
}