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
                                @if(isset($item['product']) && isset($item['product']->image))
                                    <img src="{{ $item['product']->image }}" class="h-10 w-10 rounded-md object-cover" />
                                @else
                                    <span class="text-gray-400">Нет фото</span>
                                @endif
                            </td>

                            <td class="px-4 py-2 text-xs text-gray-900 dark:text-gray-100">
                                {{ $item['product']->name ?? '—' }}
                            </td>

                            <td class="px-4 py-2">
                                {{-- {{ $item['product']->sku ?? '—' }} --}}

                                <select
                                    class=" border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                    wire:model.live="itemsByOrderId.{{ $order->id }}.{{ $index }}.product_sku">
                                    <option value="{{ $item['product']->sku ?? '' }}">{{ $item['product']->sku ?? 'Выберите товар' }}</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product }}">{{ $product }}</option>
                                    @endforeach
                                </select>


                            </td>

                            <td class="px-4 py-2">
                                {{ $item['product']->stock_quantity ?? '—' }}
                            </td>

                            <td class="px-4 py-2">
                                {{ $item['quantity'] ?? '—' }}
                            </td>

                            <td class="px-4 py-2">
                                @if(isset($item['product']) && isset($item['product']->desired_stock_quantity))
                                    {{ $item['product']->desired_stock_quantity }}
                                @else
                                    —
                                @endif
                            </td>

                            <td class="px-4 py-2">
                                <input
                                    type="number"
                                    wire:model.live="itemsByOrderId.{{ $order->id }}.{{ $index }}.required_quantity"
                                    min="0"
                                    class="w-20 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md"
                                />
                            </td>

                            <td class="px-4 py-2">
                                <select
                                    wire:model.live="itemsByOrderId.{{ $order->id }}.{{ $index }}.factory_id"
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


        <!-- Диалоговое окно подтверждения перезаписи -->
        @if($showOverwriteDialog)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             x-data="{ show: true }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md mx-4 shadow-2xl border border-gray-200 dark:border-gray-700"
                 x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <h3 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">
                        Подтверждение перезаписи
                    </h3>
                </div>

                <div class="mb-4">
                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-3 font-medium">
                        Найдены существующие заказы на производство, которые будут перезаписаны:
                    </p>

                    <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-3 max-h-40 overflow-y-auto border border-gray-200 dark:border-gray-600">
                        @foreach($conflictingOrders as $conflict)
                        <div class="text-sm text-gray-900 dark:text-gray-200 mb-2 p-2 bg-white dark:bg-gray-600 rounded border border-gray-100 dark:border-gray-500">
                            <strong class="text-gray-900 dark:text-white">Заказ №{{ $conflict['order_number'] }}</strong>
                            <br>
                            <span class="text-gray-700 dark:text-gray-300">
                                Существующие заказы: <span class="font-semibold">{{ $conflict['existing_count'] }}</span>
                                @if($conflict['factories'])
                                <br>Фабрики: <span class="font-medium">{{ $conflict['factories'] }}</span>
                                @endif
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-2 border-t border-gray-200 dark:border-gray-600">
                    <button
                        type="button"
                        wire:click="cancelOverwrite"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:focus:ring-gray-500 font-medium transition-all duration-200 border border-gray-300 dark:border-gray-500">
                        Отменить
                    </button>

                    <button
                        type="button"
                        wire:click="confirmOverwrite"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:focus:ring-gray-500 font-medium transition-all duration-200 border border-gray-300 dark:border-gray-500">
                        Перезаписать
                    </button>
                </div>
            </div>
        </div>
        @endif

    </form>
</x-filament::page>
