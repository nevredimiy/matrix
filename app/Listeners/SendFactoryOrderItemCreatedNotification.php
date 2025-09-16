<?php

namespace App\Listeners;

use App\Events\FactoryOrderItemCreated;
use App\Services\TelegramNotificationService;
// make synchronous

class SendFactoryOrderItemCreatedNotification
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
    public function handle(FactoryOrderItemCreated $event): void
    {
        $this->telegramService->notifyFactoryOrderItemCreated($event->orderItem);
    }
}
