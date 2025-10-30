<?php

namespace Dpb\Departments\Services;

use Dpb\DatahubSync\Models\EmployeeContract;
use Dpb\Departments\Models\Department;
use Dpb\DpbUtils\Helpers\UserPermissionHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class DepartmentService
{
    public const SESSION_KEY_ACTIVE_DEPARTMENT = 'dpb_departments_active_department_id';

    public static function getAvailableDepartments(): Collection
    {
        if (UserPermissionHelper::hasPermission(permission: 'dpb-departments.department.read_all')) {
            return self::getAllDepartments();
        } elseif (UserPermissionHelper::hasPermission(permission: 'dpb-departments.department.read_assigned')) {
            return self::getAssignedDepartments();
        } else {
            return new Collection(items: []);
        }
    }

    public static function getActiveDepartment(): ?Department
    {
        return self::getActiveDepartmentFromSession();
    }

    public static function setActiveDepartment(
        int|Department $department
    ): void {
        self::storeActiveDepartmentToSession(department: $department);
    }

    public static function getEmployeeContractsOfActiveDepartment(): Collection
    {
        return EmployeeContract::whereHas(
            relation: 'department',
            callback: function (Builder $query): void {
                $query->where(column: 'is_active', operator: 1)
                    ->where(column: 'id', operator: self::getActiveDepartment()?->id ?? 0);
            }
        )
            ->whereHas(
                relation: 'circuit',
                callback: function (Builder $query): void {
                    $query->whereIn(column: 'code', values: array_merge(config(key: 'dpb-em.allowed_circuit_codes')));
                }
            )
            ->get();
    }



    public static function getMinCatalogingQuota(
        ?int $departmentCode = null
    ): int {
        $config = config(key: 'dpb-departments.min_cataloging_quota', default: []);
        $departmentCode = $departmentCode ?? self::getActiveDepartment()?->code;
        return array_key_exists(key: $departmentCode, array: $config)
            ? $config[$departmentCode]
            : $config['default']
                ?? throw new \RuntimeException(message: 'No default min_cataloging_quota configured in dpb-departments config file.');
    }

    private static function getDefaultAvailableDepartment(): ?Department
    {
        return self::getAvailableDepartments()
            ->first();
    }

    public static function getActiveDepartmentId(): int
    {
        return Session::get(key: static::SESSION_KEY_ACTIVE_DEPARTMENT) ?? 0;
    }

    private static function getActiveDepartmentFromSession(): ?Department
    {
        if (empty($departmentId = Session::get(key: static::SESSION_KEY_ACTIVE_DEPARTMENT))) {
            $defaultDepartment = self::getDefaultAvailableDepartment();
            if (!empty($defaultDepartment = self::getDefaultAvailableDepartment())) {
                self::setActiveDepartment(department: $defaultDepartment);
                return $defaultDepartment;
            } else {
                return null;
            }
        }
        if (empty($department = Department::find(id: $departmentId))) {
            Session::forget(keys: static::SESSION_KEY_ACTIVE_DEPARTMENT);
            return null;
        }
        return $department;
    }

    private static function storeActiveDepartmentToSession(
        int|Department $department
    ): void {
        Session::put(
            key: static::SESSION_KEY_ACTIVE_DEPARTMENT,
            value: ($department instanceof Department)
                ? $department->id :
                $department
        );
    }

    private static function getAllDepartments(): Collection
    {
        $codes = config(key: 'dpb-em.allowed_circuit_codes');
        $results = collect(value: DB::select(query: "
            SELECT CO.datahub_department_id, COUNT(0) AS `count`
            FROM datahub_employee_contracts CO
            LEFT JOIN datahub_employee_circuits CI ON CO.circuit_id = CI.id
            WHERE CO.is_active = 1
            AND CI.code IN (".implode(separator: ',', array: array_fill(start_index: 0, count: count(value: $codes), value: '?')).")
            GROUP BY CO.datahub_department_id
            ORDER BY `count`
        ", bindings: $codes));
        return Department::whereIn('id', $results->pluck(value: 'datahub_department_id'))
            ->get();
    }

    private static function getAssignedDepartments(): Collection
    {
        return Department::whereIn('id', Auth::user()->properties['available-departments'] ?? [])
            ->get();
    }

    public static function findEmployeeContractsOfDemandedDepartments(
        array $departmentIds
    ): Collection {
        return EmployeeContract::with(relations: ['employee', 'circuit', 'department'])
            ->whereHas(
                relation: 'department',
                callback: function (Builder $query) use ($departmentIds): void {
                    $query->where(column: 'is_active', operator: 1)
                        ->whereIn(column: 'id', values: $departmentIds);
                }
            )
            ->whereHas(
                relation: 'circuit',
                callback: function (Builder $query): void {
                    $query->whereIn(column: 'code', values: array_merge(arrays: config(key: 'dpb-em.allowed_circuit_codes')));
                }
            )
            ->get();
    }

    public static function findAllAvailableEmployeeContracts(): Collection
    {
        $departments = self::getAvailableDepartments()
            ->pluck('id')
            ->toArray();
        return self::findEmployeeContractsOfDemandedDepartments($departments);
    }
}
