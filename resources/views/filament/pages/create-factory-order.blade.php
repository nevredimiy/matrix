<x-filament::page>
    <form wire:submit.prevent="save">
        <div class="flex justify-end mb-2">
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
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">К производству</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach ($this->items as $index => $item)
                        <tr>
                            <td class="px-4 py-2">
                                @if ($item['image'])
                                    <img src="{{ $item['image'] }}" alt="" class="h-10 w-10 rounded-md object-cover">
                                @else
                                    <span class="text-gray-400">Нет фото</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                {{ $item['product_name'] }}
                            </td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                <input 
                                    type="text" 
                                    wire:model.live="items.{{ $index }}.product_sku"
                                    class="w-24 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                    value="{{ $item['product_sku'] ?? ''}}"
                                />
                                
                            </td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                {{ $item['stock_quantity'] }}
                            </td>
                            <td class="px-4 py-2">
                                <input
                                    type="number"
                                    wire:model="items.{{ $index }}.quantity"
                                    min="1"
                                    class="w-24 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-2">
            <button
                type="button"
                wire:click="addEmptyItem"
                class="mb-4 px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700"
            >
                   Добавить товар
            </button>
        </div>
       
    </form>
</x-filament::page>
