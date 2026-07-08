<x-filament-panels::page>
        <!-- Debug info -->
        @php
            \Log::debug('View rendered - Actions available:', [
                'actions' => method_exists($this, 'getFormActions') ? 'yes' : 'no',
                'has_save' => isset($this->save) ? 'yes' : 'no'
            ]);
        @endphp    
    {{ $this->form }}
    
    <div class="flex justify-end mt-6 gap-3">
        {{ $this->save }}
    </div>
</x-filament-panels::page>