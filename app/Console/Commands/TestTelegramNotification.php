<?php

namespace App\Console\Commands;

use App\Services\TelegramNotificationService;
use Illuminate\Console\Command;

class TestTelegramNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:test {--message= : Custom test message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Telegram notification system';

    protected $telegramService;

    public function __construct(TelegramNotificationService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Telegram notification system...');

        if ($this->option('message')) {
            $message = $this->option('message');
            $this->info("Sending custom message: {$message}");
            
            $result = $this->telegramService->sendMessage($message);
        } else {
            $this->info('Sending test notification...');
            
            $result = $this->telegramService->notify(
                '🧪 Тестовое уведомление',
                'Система уведомлений Telegram работает корректно!',
                [
                    'Время' => now()->format('d.m.Y H:i:s'),
                    'Статус' => 'Успешно'
                ]
            );
        }

        if ($result) {
            $this->info('✅ Уведомление отправлено успешно!');
        } else {
            $this->error('❌ Ошибка при отправке уведомления. Проверьте настройки.');
        }
    }
}
