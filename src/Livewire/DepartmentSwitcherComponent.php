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
    use InteractsWithActions;
    use InteractsWithForms;
    use HasComponentGuard;
    use HasDepartmentService;

    public const EVENT_DEPARTMENT_CHANGED = 'dpb_departments_selected_department_changed_event';

    public string $activeDepartmentId = '';

    #[Computed()]
    public function availableDepartments(): array
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
    public function showScrollbar(): bool
    {
        return count(value: $this->availableDepartments()) < $this->getMinimumDepartmentsToShowModal();
    }

    #[Computed()]
    public function showModal(): bool
    {
        return count(value: $this->availableDepartments()) >= $this->getMinimumDepartmentsToShowModal();
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

    public function mount(
    ): void {
        try {
            $this->activeDepartmentId = $this
                ->getDepartmentService()
                ->getActiveDepartment()?->id ?? '';
        } catch (RuntimeException $ex) {

        }
    }

    public function switchDepartment(
        int $departmentId
    ): void {
        $this->activeDepartmentId = $departmentId;
        $this->getDepartmentService()
            ->setActiveDepartment(
                department: $departmentId
            );
        $this->dispatch(
            event: static::EVENT_DEPARTMENT_CHANGED,
            departmentId: $departmentId
        );
    }

    public function switchDepartmentAndCloseModal(
        int $departmentId
    ): void {
        $this->switchDepartment(departmentId: $departmentId);
        $this->closeActionModal();
    }

    public function render()
    {
        return view(view: 'dpb-departments::livewire.department-switcher-component');
    }

    public function openFullDepartmentSwitcherAction(): Action
    {
        return Action::make(name: 'openFullDepartmentSwitcherAction')
            ->label(label: $this->getActiveDepartmentCode())
            ->icon(icon: count($this->availableDepartments()) > 1 ? 'heroicon-o-chevron-down' : '')
            ->visible(condition: fn (): bool => $this->showModal())
            ->modalContent(content: view(view: 'dpb-departments::livewire.department-switcher-modal-action', data: [
                'activeDepartmentId' => $this->activeDepartmentId
            ]));
    }
}
