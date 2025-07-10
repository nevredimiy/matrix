<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\OcProduct;
use App\Models\OcOrder;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Hidden;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ProductionOrderBuilder extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.production-order-builder';

    protected static ?string $navigationLabel = 'Заказ на производство';

    protected static ?string $navigationGroup = 'Общие настройки';

    public ?array $products = [];
    public  $orders = null;

    public function mount(): void
    {
        $products = OcProduct::whereNotNull('ean')
            ->where('ean', '!=', '')
            ->whereIn('product_id', function ($query) {
                $query->select('product_id')
                    ->from('order_product'); // Обрати внимание на префикс таблицы, если есть (например, 'oc_order_product')
            })
            ->withSum('orderProducts as total_ordered', 'quantity') // добавляем сумму
            ->take(50)
            ->get()
            ->map(fn($product) => [
                'product_id' => $product->product_id,
                'model' => $product->model,
                'sku' => $product->sku,
                'total_ordered' => $product->total_ordered ?? 0, // сумма заказов
                'selected' => false,
                'quantity' => $product->quantity,
                'quantity_to_order' => 0,
            ])
            ->toArray();

        $this->form->fill([
            'products' => $products,
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Repeater::make('products')
                ->label('Товары')
                ->schema([
                    Hidden::make('product_id'),
                    Checkbox::make('selected')->label(false)->columnSpan(1),
                    TextInput::make('model')->label('Модель')->disabled()->columnSpan(4),
                    TextInput::make('sku')->label('SKU')->disabled()->columnSpan(3),
                    TextInput::make('quantity')->label('На складе')->disabled()->columnSpan(2),
                    TextInput::make('total_ordered')->label('В заказах')->disabled()->columnSpan(2),
                    TextInput::make('quantity_to_order')->numeric()->minValue(0)->label('Заказать')->columnSpan(2),
                ])
                ->columns(14)
                ->addable(false)
                ->deletable(false)
                ->reorderable(false)
                ->columnSpanFull(),
        ];
    }

    public function submit()
    {
        $selected = collect($this->products)
            ->filter(fn($item) => $item['selected'] && $item['quantity'] > 0);

        if ($selected->isEmpty()) {
            Notification::make()
                ->title('Выберите хотя бы один товар')
                ->danger()
                ->send();
            return;
        }

        // Здесь можно сохранить данные в свою таблицу production_orders
        // Пример:
        foreach ($selected as $item) {
            DB::table('production_orders')->insert([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'created_at' => now(),
            ]);
        }

        Notification::make()
            ->title('Заказ на производство создан')
            ->success()
            ->send();

        $this->redirect('/admin'); // или куда тебе нужно
    }
}
