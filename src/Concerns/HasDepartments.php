<?php

namespace Dpb\Departments\Concerns;

use Dpb\Departments\Models\Department;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasDepartments
{
    public function departments(): MorphToMany
    {
        return $this->morphToMany(
            Department::class,
            'morphable',
            'dpb_departments_mm_morphable_department',
            'morphable_id',
            'department_id'
        );
    }

    public function attachDepartment(
        int|Department $department
    ): static {
        $this->departments()
            ->syncWithoutDetaching(
                ids: $department instanceof Department
                    ? $department->id
                    : $department
            );
        return $this;
    }

    public function detachDepartment(
        int|Department $department
    ): static {
        $this->departments()
            ->detach(
                ids: $department instanceof Department
                    ? $department->id
                    : $department
            );
        return $this;
    }

    public function syncDepartments(
        array $departments
    ): static {
        $this->departments()
            ->sync(ids: $departments);
        return $this;
    }

    public function getDepartments(): Collection
    {
        return $this->departments()
            ->get();
    }
}
