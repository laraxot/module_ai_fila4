<x-filament-panels::page>
    {{-- Form di fine-tuning --}}
    <x-filament-panels::form wire:submit.prevent="startFineTuning">
        {{-- Renderizza il form generato nel controller --}}
        {{ $this->form }}

        {{-- Azioni del form (pulsanti come Avvia Fine-Tuning) --}}
        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
    </x-filament-panels::form>
</x-filament-panels::page>
