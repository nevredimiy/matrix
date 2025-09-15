<?php

namespace App\Listeners;

use App\Events\FactoryOrderCreated;
use App\Services\TelegramNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendFactoryOrderCreatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    protected $telegramService;

    /**
     * Create the event listener.
     */
    public function __construct(TelegramNotificationService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle the event.
     */
    public function handle(FactoryOrderCreated $event): void
    {
        $this->telegramService->notifyFactoryOrderCreated($event->factoryOrder);
    }
}
