<div class="flex items-center justify-between">
    <!-- Modal Button (Hamburger Menu) -->
    @if ($this->showModal())
        <div class="mr-4 flex items-center">
            @if ($this->openFullDepartmentSwitcherAction->isVisible())
                {{ $this->openFullDepartmentSwitcherAction() }}
            @endif
            <x-filament-actions::modals />
        </div>
    @endif

</div>
