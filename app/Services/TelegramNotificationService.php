<?php

namespace App\Services;

use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class TelegramNotificationService
{
    protected $chatId;
    protected $botName;
    protected $botToken;

    public function __construct()
    {
        $this->botName = config('telegram.default', 'mybot');
        $this->chatId = Setting::get('telegram_chat_id', config('telegram.bots.mybot.chat_id', env('TELEGRAM_CHAT_ID')));
        $this->botToken = Setting::get('telegram_bot_token', config("telegram.bots.{$this->botName}.token"));

        if (!empty($this->botToken)) {
            // Override bot token at runtime from settings
            config(["telegram.bots.{$this->botName}.token" => $this->botToken]);
        }
    }


    /**
     * Отправка сообщения в Telegram
     */
    public function sendMessage(string $message, string $parseMode = 'HTML'): bool
    {
        try {
            if (empty($this->chatId)) {
                Log::warning('Telegram chat ID not configured');
                return false;
            }

            $response = Telegram::bot($this->botName)->sendMessage([
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => $parseMode,
            ]);

            Log::info('Telegram message sent successfully', [
                'message_id' => $response->getMessageId(),
                'chat_id' => $this->chatId
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send Telegram message', [
                'error' => $e->getMessage(),
                'chat_id' => $this->chatId
            ]);
            return false;
        }
    }

    /**
     * Уведомление о создании заказа на производство
     */
    public function notifyFactoryOrderCreated($factoryOrder): bool
    {
        $message = "🏭 <b>Новый заказ на производство</b>\n\n";
        $message .= "📋 Номер заказа: <code>{$factoryOrder->order_number}</code>\n";
        $message .= "🏢 Фабрика: <b>{$factoryOrder->factory->name}</b>\n";
        $message .= "📅 Дата создания: " . now()->format('d.m.Y H:i') . "\n";
        $message .= "📊 Статус: <b>{$factoryOrder->status}</b>\n";
        
        if ($factoryOrder->items->count() > 0) {
            $message .= "\n📦 Позиции заказа:\n";
            foreach ($factoryOrder->items as $item) {
                $message .= "• " . ($item->product->name ?? 'Товар #' . $item->product_id) . " - {$item->quantity_ordered} шт.\n";
            }
        }

        return $this->sendMessage($message);
    }

    /**
     * Уведомление об изменении статуса заказа на производство
     */
    public function notifyFactoryOrderStatusChanged($factoryOrder, $oldStatus): bool
    {
        $message = "🔄 <b>Изменение статуса заказа на производство</b>\n\n";
        $message .= "📋 Номер заказа: <code>{$factoryOrder->order_number}</code>\n";
        $message .= "🏢 Фабрика: <b>{$factoryOrder->factory->name}</b>\n";
        $message .= "📊 Статус: <b>{$oldStatus}</b> → <b>{$factoryOrder->status}</b>\n";
        $message .= "📅 Время изменения: " . now()->format('d.m.Y H:i');

        return $this->sendMessage($message);
    }

    /**
     * Уведомление о создании обычного заказа
     */
    public function notifyOrderCreated($order): bool
    {
        $message = "🛒 <b>Новый заказ</b>\n\n";
        $message .= "📋 Номер заказа: <code>{$order->order_number}</code>\n";
        $message .= "🏪 Магазин: <b>" . ($order->store->name ?? 'Не указан') . "</b>\n";
        $message .= "📅 Дата заказа: " . ($order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d.m.Y H:i') : now()->format('d.m.Y H:i')) . "\n";
        $message .= "📊 Статус: <b>{$order->status}</b>\n";
        
        if ($order->orderProducts->count() > 0) {
            $message .= "\n📦 Товары в заказе:\n";
            foreach ($order->orderProducts as $orderProduct) {
                $message .= "• " . ($orderProduct->product->name ?? 'Товар #' . $orderProduct->product_id) . " - {$orderProduct->quantity} шт.\n";
            }
        }

        return $this->sendMessage($message);
    }

    /**
     * Уведомление о доставке товара
     */
    public function notifyDeliveryCreated($delivery): bool
    {
        $message = "🚚 <b>Новая доставка</b>\n\n";
        $message .= "📦 Товар: <b>" . ($delivery->factoryOrderItem->product->name ?? 'Товар #' . $delivery->factoryOrderItem->product_id) . "</b>\n";
        $message .= "📋 Заказ: <code>{$delivery->factoryOrderItem->factoryOrder->order_number}</code>\n";
        $message .= "📊 Количество: <b>{$delivery->quantity}</b> шт.\n";
        $message .= "👤 Ответственный: <b>" . ($delivery->user->name ?? 'Не указан') . "</b>\n";
        $message .= "📅 Время доставки: " . $delivery->delivered_at->format('d.m.Y H:i');
        
        if ($delivery->notes) {
            $message .= "\n📝 Примечания: {$delivery->notes}";
        }

        return $this->sendMessage($message);
    }

    /**
     * Уведомление о создании позиции заказа на производство
     */
    public function notifyFactoryOrderItemCreated($orderItem): bool
    {
        $message = "📦 <b>Добавлена позиция в заказ на производство</b>\n\n";
        $message .= "📋 Заказ: <code>{$orderItem->factoryOrder->order_number}</code>\n";
        $message .= "🏢 Фабрика: <b>{$orderItem->factoryOrder->factory->name}</b>\n";
        $message .= "🛍️ Товар: <b>" . ($orderItem->product->name ?? 'Товар #' . $orderItem->product_id) . "</b>\n";
        $message .= "📊 Количество: <b>{$orderItem->quantity_ordered}</b> шт.\n";
        $message .= "📅 Дата добавления: " . now()->format('d.m.Y H:i');

        return $this->sendMessage($message);
    }

    /**
     * Универсальное уведомление
     */
    public function notify(string $title, string $description, array $details = []): bool
    {
        $message = "🔔 <b>{$title}</b>\n\n";
        $message .= "{$description}\n";
        
        if (!empty($details)) {
            $message .= "\n📋 Детали:\n";
            foreach ($details as $key => $value) {
                $message .= "• <b>{$key}:</b> {$value}\n";
            }
        }
        
        $message .= "\n📅 " . now()->format('d.m.Y H:i');

        return $this->sendMessage($message);
    }
}
