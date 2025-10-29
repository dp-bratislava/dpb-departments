<?php

namespace Dpb\Departments\Livewire;

use Dpb\Departments\Services\DepartmentService;
use Dpb\MasterPermissionGuard\Concerns\HasComponentGuard;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DepartmentSwitcherComponent extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    use HasComponentGuard;

    private const MINIMUM_DEPARTMENTS_TO_SHOW_MODAL = 7;

    public const EVENT_DEPARTMENT_CHANGED = 'dpb_departments_selected_department_changed_event';

    public string $activeDepartmentId = '';

    #[Computed()]
    public function availableDepartments(): array
    {
        return DepartmentService::getAvailableDepartments()->toArray();
    }

    public function mount(
    ): void {
        $this->activeDepartmentId = DepartmentService::getActiveDepartment()?->id ?? '';
    }

    public function switchDepartment(
        int $departmentId
    ): void {
        $this->activeDepartmentId = $departmentId;
        DepartmentService::setActiveDepartment($departmentId);
        $this->dispatch(
            event: static::EVENT_DEPARTMENT_CHANGED,
            departmentId: $departmentId
        );
    }

    public function switchDepartmentAndCloseModal(
        int $departmentId
    ): void {
        $this->switchDepartment($departmentId);
        $this->closeActionModal();
    }

    public static function getActiveDepartmentId(): int
    {
        return DepartmentService::getActiveDepartment()?->id ?? 0;
    }

    public function render()
    {
        return view('dpb-departments::livewire.department-switcher-component');
    }

    public function openFullDepartmentSwitcherAction(): Action
    {
        return Action::make('openFullDepartmentSwitcherAction')
            ->label(false)
            ->icon('heroicon-o-bars-3')
            ->visible(fn () => count($this->availableDepartments()) >= self::MINIMUM_DEPARTMENTS_TO_SHOW_MODAL)
            ->modalContent(view('dpb-departments::livewire.department-switcher-modal-action', [
                'activeDepartmentId' => $this->activeDepartmentId
            ]));
    }

}
