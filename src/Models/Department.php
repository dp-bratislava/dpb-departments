<?php

namespace Dpb\Departments\Models;

use Dpb\DatahubSync\Models\Department as DatahubDepartment;
use Dpb\DpbUtils\Concerns\HasModelMetaAttributes;
use Illuminate\Database\Eloquent\Builder;

class Department extends DatahubDepartment
{
    use HasModelMetaAttributes;

    public function getCatalogingQuota(): float
    {
        return $this->getMetaAttribute(
            key: 'min_cataloging_quota',
            default: config(key: 'dpb-departments.default_min_cataloging_quota', default: 0.0)
        );
    }

    public function setMinCatalogingQuota(
        float $percent
    ): void {
        $this->setMetaAttribute(
            metaKey: 'min_cataloging_quota',
            metaValue: $percent
        );
    }

    public function groups()
    {
        return $this->belongsToMany(
            DepartmentGroup::class,
            'dpb_departments_department_group',
            'department_id',
            'group_id',
        );
    }

    public function scopeByGroupUri(Builder $query, string $uri): Builder
    {
        return $query->whereHas('groups', function ($q) use ($uri) {
            $q->byGroupUri($uri);
        });
    }

    public function scopeByGroupUris(Builder $query, array $uris): Builder
    {
        return $query->whereHas('groups', function ($q) use ($uris) {
            $q->byGroupUris($uris);
        });
    }
}
