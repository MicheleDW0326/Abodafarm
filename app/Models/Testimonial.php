<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'role',
        'content',
        'image',
        'rating',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }

    public function getAvatarAttribute(): string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }

        // Generate initials avatar
        $initials = strtoupper(substr($this->name, 0, 2));
        return "https://ui-avatars.com/api/?name={$initials}&background=10b981&color=fff&size=128";
    }
}
