<?php

namespace App\Listeners;

use App\Events\FactoryOrderItemCreated;
use App\Services\TelegramNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendFactoryOrderItemCreatedNotification implements ShouldQueue
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
    public function handle(FactoryOrderItemCreated $event): void
    {
        $this->telegramService->notifyFactoryOrderItemCreated($event->orderItem);
    }
}
