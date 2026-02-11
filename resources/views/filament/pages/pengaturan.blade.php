<x-filament-panels::page>
    {{-- Page content --}}
    <form wire:submit.prevent="save"> 
          <x-filament::actions 
            :actions="$this->getFormActions()"
        /> 
        </form>
{{ $this->form }}
</x-filament-panels::page>
