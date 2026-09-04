<?php

namespace Dpb\Departments\Livewire;

use Dpb\Departments\Concerns\HasDepartmentService;
use Dpb\MasterPermissionGuard\Concerns\HasComponentGuard;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Attributes\Computed;
use Livewire\Component;
use RuntimeException;

class DepartmentSwitcherComponent extends Component implements HasActions, HasForms
{
    use HasComponentGuard;
    use HasDepartmentService;
    use InteractsWithActions;
    use InteractsWithForms;

    public const EVENT_DEPARTMENT_CHANGED = 'dpb_departments_selected_department_changed_event';

    public string $activeDepartmentId = '';

    // Fetched ONLY when the modal content renders
    public function getAvailableDepartmentsData(): array
    {
        return $this
            ->getDepartmentService()
            ->getAvailableDepartments()
            ->toArray();
    }

    #[Computed()]
    public function getMinimumDepartmentsToShowModal(): int
    {
        return config(key: 'dpb-departments.minimum_departments_to_show_modal', default: 7);
    }

    #[Computed()]
    public function showModal(): bool
    {
        // Count departments directly via service to avoid loading full department models/arrays on mount
        return $this->getDepartmentService()->getAvailableDepartments()->count() >= $this->getMinimumDepartmentsToShowModal();
    }

    #[Computed()]
    public function getActiveDepartmentCode(): string
    {
        try {
            $activeDepartment = $this
                ->getDepartmentService()
                ->getActiveDepartment();

            return $activeDepartment?->code ?? '';
        } catch (RuntimeException $ex) {
            return '';
        }
    }

    public function mount(): void 
    {
        try {
            $this->activeDepartmentId = $this
                ->getDepartmentService()
                ->getActiveDepartment()?->id ?? '';
        } catch (RuntimeException $ex) {

        }
    }

    public function switchDepartment(int $departmentId): void 
    {
        $this->activeDepartmentId = $departmentId;
        $this->getDepartmentService()
            ->setActiveDepartment(department: $departmentId);

        $this->dispatch(
            event: static::EVENT_DEPARTMENT_CHANGED,
            departmentId: $departmentId
        );
    }

    public function switchDepartmentAndCloseModal(int $departmentId): void 
    {
        $this->switchDepartment(departmentId: $departmentId);
        $this->unmountAction(canCancelParentActions: false);
    }

    public function render()
    {
        return view('dpb-departments::livewire.department-switcher-component');
    }

    public function openFullDepartmentSwitcherAction(): Action
    {
        return Action::make('openFullDepartmentSwitcherAction')
            ->label($this->getActiveDepartmentCode())
            ->icon($this->getDepartmentService()->getAvailableDepartments()->count() > 1 ? 'heroicon-o-chevron-down' : '')
            ->visible(fn (): bool => $this->showModal)
            ->modalSubmitAction(false)
            ->modalContent(fn () => view('dpb-departments::livewire.department-switcher-modal-action', [
                'activeDepartmentId' => $this->activeDepartmentId,
                'availableDepartments' => $this->getAvailableDepartmentsData(),
            ]));
    }
}