<x-filament-panels::page>
    <form wire:submit="submit">
        <div class="mb-4">
            {{ $this->form }}
        </div>
        
        <x-filament::button type="submit">
            Сохранить
        </x-filament::button>
    </form>
</x-filament-panels::page>
