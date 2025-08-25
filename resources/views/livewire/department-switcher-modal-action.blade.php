<div class="grid grid-cols-2">
    @forelse ($this->availableDepartments as $department)
        <div>
            <x-filament::button
                title="{{ $department['title'] }}"
                class="mx-1 my-2 inline-block"
                color="{{ $department['id'] == $activeDepartmentId ? 'primary' : 'gray' }}"
                wire:click="switchDepartmentAndCloseModal('{{ $department['id'] }}')"
            >
                {{ $department['code'] }} - {{ $department['title'] }}
            </x-filament::button>
        </div>
    @empty
        {{ __('dpb-wtff::department-switcher-component.messages.no_deapartments_available') }}
    @endforelse
</div>
