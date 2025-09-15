<?php

namespace App\Listeners;

use App\Events\FactoryOrderStatusChanged;
use App\Services\TelegramNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendFactoryOrderStatusChangedNotification implements ShouldQueue
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
    public function handle(FactoryOrderStatusChanged $event): void
    {
        $this->telegramService->notifyFactoryOrderStatusChanged($event->factoryOrder, $event->oldStatus);
    }
}
