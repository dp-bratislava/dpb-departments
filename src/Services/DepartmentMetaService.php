<?php

namespace Dpb\Departments\Services;

use Illuminate\Support\Facades\DB;
use stdClass;

class DepartmentMetaService
{
    public function findAllDepartmentMetaData(
        int $departmentId
    ): array {
        return DB::table(table: 'dpb_departments_mm_departmentmeta')
            ->where(column: 'department_id', operator: '=', value: $departmentId)
            ->get()
            ->mapWithKeys(callback: fn (stdClass $item): array => [$item->param_name => $item->param_value])
            ->toArray();
    }

    public function getMeta(
        string $paramName,
        int $departmentId,
        mixed $defaultValue = null
    ): mixed {
        return DB::table(table: 'dpb_departments_mm_departmentmeta')
            ->where(column: 'department_id', operator: '=', value: $departmentId)
            ->where(column: 'param_name', operator: '=', value: $paramName)
            ->value(column: 'param_value') ?? $defaultValue;
    }

    public function setMeta(
        string $paramName,
        int $departmentId,
        mixed $paramValue
    ): void {
        DB::table(table: 'dpb_departments_mm_departmentmeta')
            ->updateOrInsert(
                attributes: [
                    'department_id' => $departmentId,
                    'param_name' => $paramName,
                ],
                values: [
                    'param_value' => $paramValue,
                ]
            );
    }
}
