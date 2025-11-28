<?php

namespace Dpb\Departments\Concerns;

use Dpb\Departments\Services\DepartmentService;

trait HasDepartmentService
{
    protected DepartmentService $departmentService;

    public function bootHasDepartmentService(
        DepartmentService $departmentService
    ): void {
        $this->departmentService = $departmentService;
    }

    public function getDepartmentService(): DepartmentService
    {
        return $this->departmentService;
    }
}
