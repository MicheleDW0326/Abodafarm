<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    // Relations for Peternak
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'owner_id');
    }

    public function farmProfile(): BelongsTo
    {
        return $this->belongsTo(FarmProfile::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(PeternakNotification::class);
    }

    public function unreadNotifications(): HasMany
    {
        return $this->notifications()->where('is_read', false);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // Customer Relations
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function defaultAddress(): ?Address
    {
        return $this->addresses()->where('is_default', true)->first();
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    public function favoriteProducts(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'favorites', 'user_id', 'favoritable_id')
            ->where('favoritable_type', Product::class);
    }

    public function customerNotifications(): HasMany
    {
        return $this->hasMany(CustomerNotification::class);
    }

    public function unreadCustomerNotifications(): HasMany
    {
        return $this->customerNotifications()->where('is_read', false);
    }

    public function unreadNotificationsCount(): int
    {
        return $this->customerNotifications()->where('is_read', false)->count();
    }

    // Review Relations

    /**
     * Get reviews made by this user.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get reviews for products owned by this user (as farmer).
     */
    public function productReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'farmer_id');
    }

    /**
     * Get average rating as farmer.
     */
    public function getFarmerRatingAttribute(): float
    {
        $reviews = $this->productReviews()->approved()->get();

        if ($reviews->isEmpty()) {
            return 0;
        }

        return round($reviews->avg('rating'), 1);
    }

    /**
     * Get total reviews as farmer.
     */
    public function getTotalFarmerReviewsAttribute(): int
    {
        return $this->productReviews()->approved()->count();
    }
}
