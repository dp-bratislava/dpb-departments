<?php

namespace Dpb\Departments\Services;

use Illuminate\Support\Facades\DB;

class DepartmentMetaService
{
    public function findAllDepartmentMetaData(
        int $departmentId
    ): array {
        return DB::table('dpb_departments_mm_departmentmeta')
            ->where('department_id', '=', $departmentId)
            ->get()
            ->mapWithKeys(fn ($item) => [$item->param_name => $item->param_value])
            ->toArray();
    }

    public function getMeta(
        string $paramName,
        int $departmentId,
        mixed $defaultValue = null
    ): mixed {
        return DB::table('dpb_departments_mm_departmentmeta')
            ->where('department_id', '=', $departmentId)
            ->where('param_name', '=', $paramName)
            ->value('param_value') ?? $defaultValue;
    }

    public function setMeta(
        string $paramName,
        int $departmentId,
        mixed $paramValue
    ): void {
        DB::table('dpb_departments_mm_departmentmeta')
            ->updateOrInsert(
                [
                    'department_id' => $departmentId,
                    'param_name' => $paramName,
                ],
                [
                    'param_value' => $paramValue,
                ]
            );
    }
}
