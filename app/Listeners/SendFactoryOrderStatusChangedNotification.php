<?php

namespace App\Listeners;

use App\Events\FactoryOrderStatusChanged;
use App\Services\TelegramNotificationService;
// make synchronous

class SendFactoryOrderStatusChangedNotification
{

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
