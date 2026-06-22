<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'label',
        'icon',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public static function getByKey(string $key): ?self
    {
        return static::where('key', $key)->first();
    }
}
