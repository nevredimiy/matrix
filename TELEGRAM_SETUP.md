# Настройка Telegram уведомлений

## Шаги для настройки:

### 1. Создание Telegram бота

1. Откройте Telegram и найдите бота @BotFather
2. Отправьте команду `/newbot`
3. Введите имя для вашего бота (например: "Dinara CRM Notifications")
4. Введите username для бота (например: "dinara_crm_bot")
5. Скопируйте полученный токен

### 2. Получение Chat ID

1. Добавьте созданного бота в группу или начните с ним диалог
2. Отправьте любое сообщение боту
3. Откройте в браузере: `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates`
4. Найдите в ответе `"chat":{"id":-123456789}` - это ваш Chat ID

### 3. Настройка переменных окружения

Добавьте в файл `.env` следующие переменные:

```env
# Telegram Bot Configuration
TELEGRAM_BOT_TOKEN=your_telegram_bot_token_here
TELEGRAM_CHAT_ID=your_telegram_chat_id_here
TELEGRAM_ASYNC_REQUESTS=false
```

### 4. Настройка очереди (рекомендуется)

Для асинхронной отправки уведомлений настройте очередь:

```env
QUEUE_CONNECTION=database
```

Затем выполните:
```bash
php artisan queue:table
php artisan migrate
```

### 5. Запуск воркера очереди

```bash
php artisan queue:work
```

## События, которые отслеживаются:

- ✅ Создание заказа на производство (`FactoryOrder`)
- ✅ Изменение статуса заказа на производство
- ✅ Создание обычного заказа (`Order`)
- ✅ Создание позиции заказа на производство (`FactoryOrderItem`)
- ✅ Создание доставки (`FactoryOrderDelivery`)

## Тестирование

Для тестирования уведомлений можно использовать tinker:

```bash
php artisan tinker
```

```php
// Тест создания заказа на производство
$factory = App\Models\Factory::first();
$order = App\Models\Order::first();
$factoryOrder = App\Models\FactoryOrder::create([
    'factory_id' => $factory->id,
    'order_id' => $order->id,
    'order_number' => 'TEST-' . time(),
    'status' => 'pending'
]);
```

## Структура уведомлений

Уведомления отправляются в формате HTML с эмодзи для лучшей читаемости:

- 🏭 Заказы на производство
- 🛒 Обычные заказы
- 📦 Позиции заказов
- 🚚 Доставки
- 🔄 Изменения статусов
