<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class CrmSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Настройки crm';
    protected static ?string $title = 'Настройки crm';
    protected static ?string $navigationGroup = 'Налаштування';
    protected static ?int $navigationSort = 51;

    protected static string $view = 'filament.pages.crm-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'max_days_per_form' => Setting::get('max_days_per_form', '1'),
            'telegram_bot_token' => Setting::get('telegram_bot_token', ''),
            'telegram_chat_id' => Setting::get('telegram_chat_id', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('max_days_per_form')
                    ->label('Максимум дней на форму')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('telegram_bot_token')
                    ->label('Токен бота')
                    ->password()
                    ->revealable()
                    ->required(),
                Forms\Components\TextInput::make('telegram_chat_id')
                    ->label('ID группы/чата')
                    ->required(),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $state = $this->form->getState();
        Setting::set('max_days_per_form', $state['max_days_per_form'] ?? 0);
        Setting::set('telegram_bot_token', $state['telegram_bot_token'] ?? '');
        Setting::set('telegram_chat_id', $state['telegram_chat_id'] ?? '');

        Notification::make()
            ->title('Сохранено')
            ->body('Настройки обновлены')
            ->success()
            ->send();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin');
    }
}


