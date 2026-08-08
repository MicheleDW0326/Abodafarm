<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewReport extends Model
{
    protected $fillable = [
        'review_id',
        'user_id',
        'reason',
        'description',
        'status',
        'reviewed_by',
        'admin_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public const REASON_SPAM = 'spam';
    public const REASON_FAKE = 'fake';
    public const REASON_INAPPROPRIATE = 'inappropriate';
    public const REASON_OFFENSIVE = 'offensive';
    public const REASON_OTHER = 'other';

    public const STATUS_PENDING = 'pending';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_DISMISSED = 'dismissed';

    public static array $reasons = [
        self::REASON_SPAM => 'Spam / Iklan',
        self::REASON_FAKE => 'Ulasan Palsu',
        self::REASON_INAPPROPRIATE => 'Konten Tidak Pantas',
        self::REASON_OFFENSIVE => 'Mengandung Pelanggaran',
        self::REASON_OTHER => 'Lainnya',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getReasonLabelAttribute(): string
    {
        return self::$reasons[$this->reason] ?? ucfirst($this->reason);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    public function markAsReviewed(int $adminId, string $notes, string $status = self::STATUS_RESOLVED): void
    {
        $this->update([
            'reviewed_by' => $adminId,
            'admin_notes' => $notes,
            'status' => $status,
            'reviewed_at' => now(),
        ]);
    }
}
