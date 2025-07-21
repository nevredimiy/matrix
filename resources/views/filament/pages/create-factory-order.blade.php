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
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">К производству</th>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Заказы</th>
                        <th class="px-4 py-3 text-left text-gray-600 dark:text-gray-300">Действие</th>

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
                            {{-- <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                <input 
                                    type="text" 
                                    wire:model.live="items.{{ $index }}.product_sku"
                                    class="w-24 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                    value="{{ $item['product_sku'] ?? ''}}"
                                />
                                
                            </td> --}}
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                <select 
                                    class=" border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500" 
                                    wire:model.live="items.{{ $index }}.product_sku">
                                    <option value="{{ $item['product_sku'] }}">{{ $item['product_sku'] }}</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product }}">{{ $product }}</option>
                                    @endforeach
                                </select>
                                
                            </td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                {{ $item['stock_quantity'] }}
                            </td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                {{ $item['quantity'] }}
                            </td>
                            <td class="px-4 py-2">
                                <input
                                    type="number"
                                    wire:model="items.{{ $index }}.required_quantity"
                                    min="1"
                                    max="1000"
                                    size="6"
                                    class="w-20 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                />
                            </td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                {{ $item['text_order_ids'] }}
                            </td>
                            <td class="px-4 py-2 text-gray-900 dark:text-gray-100">
                                <select 
                                    class=" border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500" 
                                    wire:model="items.{{ $index }}.factory_id">
                                    {{-- <option value="{{ $factories['1'] }}">{{ $factory['name'] }}</option> --}}
                                    @foreach ($factories as $key => $factory)
                                        <option value="{{ $key }}" @if($key == 1) selected @endif>{{ $factory }}</option>
                                    @endforeach
                                </select>
                                
                            </td>
                            <td class="px-4 py-2">
                                <button
                                    type="button"
                                    wire:click="removeItem({{ $index }})"
                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200 p-1"
                                    title="Удалить товар"
                                >
                                    <svg class="size-6" width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>

                                </button>
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
