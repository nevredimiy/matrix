<x-filament::page>
    <form wire:submit.prevent="save">
        <div class="flex justify-between mb-2">
            <div class="mt-6">
               <x-filament::button type="submit">
                   Создать заказ
               </x-filament::button>
           </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Фото</th>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Название</th>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Артикул</th>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">На складе</th>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">В заказах</th>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Желаемое кол-во</th>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">К производству</th>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Заказы</th>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Действие</th>

                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($orders as $order)
                    <tr class="bg-gray-100 dark:bg-gray-900">
                        <td colspan="9" class="font-semibold text-sm px-4 py-2">
                            № заказа {{ $order->order_number }}
                        </td>
                    </tr>

                    @foreach ($itemsByOrderId[$order->id] ?? [] as $index => $item)
                        <tr>
                            <td class="px-4 py-2">
                                @if(isset($item['product']->image))
                                    <img src="{{ $item['product']->image }}" class="h-10 w-10 rounded-md object-cover" />
                                @else
                                    <span class="text-gray-400">Нет фото</span>
                                @endif
                            </td>

                            <td class="px-4 py-2 text-xs text-gray-900 dark:text-gray-100">
                                {{ $item['product']->name ?? '—' }}
                            </td>

                            <td class="px-4 py-2">
                                {{ $item['product']->sku ?? '—' }}
                            </td>

                            <td class="px-4 py-2">
                                {{ $item['product']->stock_quantity ?? '—' }}
                            </td>

                            <td class="px-4 py-2">
                                {{ $item['quantity'] ?? '—' }}
                            </td>

                            <td class="px-4 py-2">
                                {{ $item['product']->desired_stock_quantity ?? '—' }}
                            </td>

                            <td class="px-4 py-2">
                                <input
                                    type="number"
                                    wire:model.defer="itemsByOrderId.{{ $order->id }}.{{ $index }}.required_quantity"
                                    min="0"
                                    class="w-20 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md"
                                />
                            </td>

                            <td class="px-4 py-2">
                                <select
                                    wire:model.defer="itemsByOrderId.{{ $order->id }}.{{ $index }}.factory_id"
                                    class="border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md"
                                >
                                    <option value="">—</option>
                                    @foreach ($factories as $key => $factory)
                                        <option value="{{ $key }}">{{ $factory }}</option>
                                    @endforeach
                                </select>
                            </td>

                            <td class="px-4 py-2">
                                <button type="button"
                                    wire:click="removeItem({{ $order->id }}, {{ $index }})"
                                    class="w-9 h-9 text-red-600 hover:text-red-800 p-1"
                                    title="Удалить товар"
                                >
                                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach

                    <tr>
                        <td colspan="9" class="px-4 py-2">
                            <button
                                type="button"
                                wire:click="addEmptyItem({{ $order->id }})"
                                class="px-3 py-1 bg-primary-600 text-white text-sm rounded hover:bg-primary-700"
                            >
                                Добавить товар в заказ {{ $order->order_number }}
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>

            </table>
        </div>

        
      
    </form>
</x-filament::page>
