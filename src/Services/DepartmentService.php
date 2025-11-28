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
        return App::make(abstract: DepartmentService::class);
    }

    public function __construct(
        private readonly ConfigurationService $configurationService
    ) {
        logger()->info(message: 'DepartmentService instantiated');
    }

    public function __destruct()
    {
        logger()->info(message: 'DepartmentService destructed');
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
                ?->firstWhere('id', $department)
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

    /**
     * @deprecated Use instance method instead
     */
    public static function getMinCatalogingQuota(
        ?int $departmentCode = null
    ): int {
        $config = config(key: 'dpb-departments.min_cataloging_quota', default: []);
        $departmentCode = $departmentCode ?? App::make(abstract: DepartmentService::class)->getActiveDepartment()->code;
        return array_key_exists(key: $departmentCode, array: $config)
            ? $config[$departmentCode]
            : $config['default']
                ?? throw new \RuntimeException(message: 'No default min_cataloging_quota configured in dpb-departments config file.');
    }
}
