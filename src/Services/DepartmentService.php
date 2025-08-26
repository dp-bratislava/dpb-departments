<?php

namespace Dpb\Departments\Services;

use Dpb\DatahubSync\Models\EmployeeContract;
use Dpb\Departments\Models\Department;
use Dpb\DpbUtils\Helpers\UserPermissionHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class DepartmentService
{
    public const SESSION_KEY_ACTIVE_DEPARTMENT = 'dpb_departments_active_department_id';

    public static function getAvailableDepartments(): Collection
    {
        if (UserPermissionHelper::hasPermission('dpb-departments.department.read_all')) {
            return self::getAllDepartments();
        } elseif (UserPermissionHelper::hasPermission('dpb-departments.department.read_assigned')) {
            return self::getAssignedDepartments();
        } else {
            return new Collection([]);
        }
    }

    public static function getActiveDepartment(): ?Department
    {
        return self::getActiveDepartmentFromSession();
    }

    public static function setActiveDepartment(
        int|Department $department
    ): void {
        self::storeActiveDepartmentToSession($department);
    }

    public static function getEmployeeContractsOfActiveDepartment(): Collection
    {
        return EmployeeContract::whereHas(
            'department',
            function ($query) {
                $query->where('is_active', 1)
                    ->where('id', self::getActiveDepartment()?->id ?? 0);
            }
        )
            ->whereHas(
                'circuit',
                function ($query) {
                    $query->whereIn('code', array_merge(config('dpb-em.allowed_circuit_codes')));
                }
            )
            ->get();
    }



    public static function getMinCatalogingQuota(
        ?string $departmentCode = null
    ): int {
        $config = config('dpb-departments.min_cataloging_quota', []);
        $departmentCode = $departmentCode ?? self::getActiveDepartment()?->code;
        return array_key_exists($departmentCode, $config)
            ? $config[$departmentCode]
            : $config['default']
                ?? throw new \RuntimeException('No default min_cataloging_quota configured in dpb-departments config file.');
    }

    private static function getDefaultAvailableDepartment(): ?Department
    {
        return self::getAvailableDepartments()
            ->first();
    }

    public static function getActiveDepartmentId(): int
    {
        return Session::get(static::SESSION_KEY_ACTIVE_DEPARTMENT) ?? 0;
    }

    private static function getActiveDepartmentFromSession(): ?Department
    {
        if (empty($departmentId = Session::get(static::SESSION_KEY_ACTIVE_DEPARTMENT))) {
            $defaultDepartment = self::getDefaultAvailableDepartment();
            if (!empty($defaultDepartment = self::getDefaultAvailableDepartment())) {
                self::setActiveDepartment($defaultDepartment);
                return $defaultDepartment;
            } else {
                return null;
            }
        }
        if (empty($department = Department::find($departmentId))) {
            Session::forget(static::SESSION_KEY_ACTIVE_DEPARTMENT);
            return null;
        }
        return $department;
    }

    private static function storeActiveDepartmentToSession(
        int|Department $department
    ): void {
        Session::put(
            static::SESSION_KEY_ACTIVE_DEPARTMENT,
            ($department instanceof Department)
                ? $department->id :
                $department
        );
    }

    private static function getAllDepartments(): Collection
    {
        $codes = config('dpb-em.allowed_circuit_codes');
        $results = collect(DB::select("
            SELECT CO.datahub_department_id, COUNT(0) AS `count`
            FROM datahub_employee_contracts CO
            LEFT JOIN datahub_employee_circuits CI ON CO.circuit_id = CI.id
            WHERE CO.is_active = 1
            AND CI.code IN (".implode(',', array_fill(0, count($codes), '?')).")
            GROUP BY CO.datahub_department_id
            ORDER BY `count`
        ", $codes));
        return Department::whereIn('id', $results->pluck('datahub_department_id'))
            ->get();
    }

    private static function getAssignedDepartments(): Collection
    {
        return Department::whereIn('id', Auth::user()->properties['available-departments'] ?? [])
            ->get();
    }
}
