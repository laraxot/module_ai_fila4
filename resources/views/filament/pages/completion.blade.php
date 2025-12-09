<x-filament-panels::page>
    <x-filament-panels::form wire:submit="completion">
        {{ $this->completionForm }}

        <x-filament-panels::form.actions
            :actions="$this->getCompletionFormActions()"
        />

    </x-filament-panels::form>
</x-filament-panels::page>
