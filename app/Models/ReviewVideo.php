<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewVideo extends Model
{
    protected $fillable = [
        'review_id',
        'path',
        'thumbnail',
        'duration',
        'sort_order',
    ];

    protected $casts = [
        'duration' => 'integer',
        'sort_order' => 'integer',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail) {
            return null;
        }
        return asset('storage/' . $this->thumbnail);
    }

    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration) {
            return '';
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
