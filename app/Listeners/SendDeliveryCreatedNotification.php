<?php

namespace App\Listeners;

use App\Events\DeliveryCreated;
use App\Services\TelegramNotificationService;
// make synchronous

class SendDeliveryCreatedNotification
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
    public function handle(DeliveryCreated $event): void
    {
        $this->telegramService->notifyDeliveryCreated($event->delivery);
    }
}
