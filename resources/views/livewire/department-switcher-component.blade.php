<div x-data="{ open: false }"
    x-init="
        (() => {
            const el = document.getElementById('department-scrollbar');
            if (el) {
                el.addEventListener('wheel', function(e) {
                    if (e.deltaY !== 0) {
                        e.preventDefault();
                        el.scrollLeft += e.deltaY * 3;
                    }
                }, { passive: false });
            }
        })()
    "
    @mouseenter="open = true"
    @mouseleave="open = false"
    class="flex"
>
    <!-- Modal Button (Hamburger Menu) -->
    @if ($this->showModal())
        <div class="mr-4 flex items-center">
            @if ($this->openFullDepartmentSwitcherAction->isVisible())
                {{ $this->openFullDepartmentSwitcherAction() }}
            @endif
            <x-filament-actions::modals />
        </div>
    @endif

    <!-- Scrollbar View -->
    @if ($this->showScrollbar())
        <div
            id="department-scrollbar"
            :class="open 
                ? 'overflow-x-auto max-h-[50vh] whitespace-nowrap'
                : 'overflow-x-auto h-12 whitespace-nowrap'"
            class="flex gap-x-2 items-center w-full max-w-[50vh] transition-all duration-200 overflow-y-hidden"
            style="scroll-behavior: smooth;"
        >
            @forelse ($this->availableDepartments as $department)
                <x-filament::button
                    title="{{ $department['title'] }}"
                    class="mx-1 my-2 inline-block"
                    color="{{ $department['id'] == $activeDepartmentId ? 'primary' : 'gray' }}"
                    wire:click="switchDepartment('{{ $department['id'] }}')"
                >
                    {{ $department['code'] }}
                </x-filament::button>
            @empty
                {{ __('dpb-wtff::department-switcher-component.messages.no_deapartments_available') }}
            @endforelse
        </div>
    @endif
</div>
