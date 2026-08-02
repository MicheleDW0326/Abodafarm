<?php

namespace App\Providers;

use App\Events\NewBookingReceived;
use App\Events\OrderStatusChanged;
use App\Events\PaymentReceived;
use App\Listeners\NotifyFarmerNewBooking;
use App\Listeners\SendOrderStatusNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        OrderStatusChanged::class => [
            SendOrderStatusNotification::class,
        ],
        NewBookingReceived::class => [
            NotifyFarmerNewBooking::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
