<?php

namespace Dpb\Departments\Concerns;

use Dpb\DatahubSync\Models\Department;
use Dpb\Departments\Services\DepartmentService;
use Illuminate\Support\Facades\App;

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
        return $this->departmentService
            ??= App::make(abstract: DepartmentService::class);
    }
}
