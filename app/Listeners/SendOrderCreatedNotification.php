<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Services\TelegramNotificationService;
// make synchronous

class SendOrderCreatedNotification
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
    public function handle(OrderCreated $event): void
    {
        $this->telegramService->notifyOrderCreated($event->order);
    }
}
