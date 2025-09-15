<?php

namespace App\Providers;

use App\Events\DeliveryCreated;
use App\Events\FactoryOrderCreated;
use App\Events\FactoryOrderItemCreated;
use App\Events\FactoryOrderStatusChanged;
use App\Events\OrderCreated;
use App\Listeners\SendDeliveryCreatedNotification;
use App\Listeners\SendFactoryOrderCreatedNotification;
use App\Listeners\SendFactoryOrderItemCreatedNotification;
use App\Listeners\SendFactoryOrderStatusChangedNotification;
use App\Listeners\SendOrderCreatedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        FactoryOrderCreated::class => [
            SendFactoryOrderCreatedNotification::class,
        ],
        FactoryOrderStatusChanged::class => [
            SendFactoryOrderStatusChangedNotification::class,
        ],
        OrderCreated::class => [
            SendOrderCreatedNotification::class,
        ],
        DeliveryCreated::class => [
            SendDeliveryCreatedNotification::class,
        ],
        FactoryOrderItemCreated::class => [
            SendFactoryOrderItemCreatedNotification::class,
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
