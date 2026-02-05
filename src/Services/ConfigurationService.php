<?php

namespace Dpb\Departments\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ConfigurationService
{
    public const SESSION_KEY_ACTIVE_DEPARTMENT = 'dpb_departments_active_department_id';

    private array|null $allowedEmployeeCircuitsIdsCache = null;
    private User|null $authenticatedUserCache = null;

    public function getAllowedEmployeeCircuitsIds(): array
    {
        return $this->allowedEmployeeCircuitsIdsCache ??= config(
            key: 'dpb-em.allowed_circuit_codes',
            default: []
        );
    }

    public function getAuthenticatedUser(): ?User
    {
        return $this->authenticatedUserCache ??= Auth::user();
    }

    public function getAvailableDepartmentsIds(): array
    {
        if ($this->getAuthenticatedUser()?->can(abilities: 'dpb-departments.department.read_all')) {
            return ['*'];
        } else {
            return $this->getAuthenticatedUser()?->properties['available-departments'] ?? [];
        }
    }

    public function getActiveDepartmentId(): int|null
    {
        return session(key: static::SESSION_KEY_ACTIVE_DEPARTMENT);
    }

    public function setActiveDepartmentId(
        int|null $departmentId
    ): void {
        session()->put(key: static::SESSION_KEY_ACTIVE_DEPARTMENT, value: $departmentId);
    }
}
