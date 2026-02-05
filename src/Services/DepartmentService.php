<?php

namespace Dpb\Departments\Services;

use Dpb\Departments\Models\Department;
use Dpb\Departments\Services\ConfigurationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;

class DepartmentService
{
    public const SESSION_KEY_ACTIVE_DEPARTMENT = 'dpb_departments_active_department_id';

    private Collection|null $availableDepartments = null;
    private Department|null $activeDepartment = null;

    public static function getInstance(): DepartmentService
    {
        return App::make(
            abstract: DepartmentService::class
        );
    }

    public function __construct(
        private readonly ConfigurationService $configurationService
    ) {
    }

    public function getAvailableDepartments(): Collection
    {
        return $this->availableDepartments ??= $this->loadAvailableDepartments();
    }

    public function getActiveDepartment(): Department
    {
        if ($this->activeDepartment) {
            return $this->activeDepartment;
        }

        $availableDepartments = $this->getAvailableDepartments();
        $activeDepartmentIdFromSession = $this->configurationService->getActiveDepartmentId() ?? 0;

        if ($availableDepartments->contains(key: 'id', operator: '=', value: $activeDepartmentIdFromSession)) {
            $this->activeDepartment = $availableDepartments
                ->firstWhere(key: 'id', operator: '=', value: $activeDepartmentIdFromSession);
        } else {
            $this->activeDepartment = $availableDepartments->first();
        }
        return $this->activeDepartment
            ?? throw new \RuntimeException(message: 'No available departments found.');
    }

    public function setActiveDepartment(
        int|Department $department
    ): void {
        if($this->getAvailableDepartments()->contains($department instanceof Department ? $department : Department::find(id: $department)) === false) {
            throw new \RuntimeException(message: 'Department not available.');
        }
        $this->configurationService->setActiveDepartmentId(
            departmentId: ($department instanceof Department)
                ? $department->id
                : $department
        );
        $this->activeDepartment = $department instanceof Department
            ? $department
            : $this->availableDepartments
                ?->firstWhere(key: 'id', operator: '=', value: $department)
            ?? Department::find(id: $department);
    }

    private function loadAvailableDepartments(): Collection
    {
        return Department::query()
            ->when(
                value: !Gate::allows(ability: 'dpb-departments.department.read_all'),
                callback: fn (Builder $query): Builder => $query->whereIn(
                    column: 'id',
                    values: $this->configurationService->getAvailableDepartmentsIds()
                )
            )
            ->get();
    }
}
