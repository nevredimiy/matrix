<?php

require_once 'vendor/autoload.php';

use App\Services\TelegramNotificationService;

// Загружаем переменные окружения
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Создаем экземпляр сервиса
$telegramService = new TelegramNotificationService();

// Тестируем отправку сообщения
echo "Тестирование Telegram уведомлений...\n";

$result = $telegramService->notify(
    '🧪 Тестовое уведомление',
    'Система уведомлений Telegram работает корректно!',
    [
        'Время' => date('d.m.Y H:i:s'),
        'Статус' => 'Успешно'
    ]
);

if ($result) {
    echo "✅ Уведомление отправлено успешно!\n";
} else {
    echo "❌ Ошибка при отправке уведомления.\n";
    echo "Проверьте настройки в .env файле:\n";
    echo "- TELEGRAM_BOT_TOKEN\n";
    echo "- TELEGRAM_CHAT_ID\n";
}
