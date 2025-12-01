<?php

namespace Dpb\Departments\Services;

use Dpb\Departments\Models\Department;
use Dpb\Departments\Services\ConfigurationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\App;

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
        return $this->activeDepartment
            ??= Department::find(id: $this->configurationService->getActiveDepartmentId() ?? 0)
            ?? $this->getAvailableDepartments()->first()
            ?? throw new \RuntimeException(message: 'No available departments found.');
    }

    public function setActiveDepartment(
        int|Department $department
    ): void {
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
            ->whereIn(
                column: 'id',
                values: $this->configurationService->getAvailableDepartmentsIds()
            )
            ->get();
    }
}
