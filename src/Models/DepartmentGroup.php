<?php

namespace Dpb\Departments\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DepartmentGroup extends Model
{
    public function departments()
    {
        return $this->belongsToMany(
            Department::class,
            'dpb_departments_department_group',
            'group_id',
            'department_id'
        );
    }

    public function scopeByGroupUri(Builder $query, string $uri): Builder
    {
        return $query->where('uri', '=', $uri);
    }

    public function scopeByGroupUris(Builder $query, array $uris): Builder
    {
        return $query->whereIn('uri', $uris);
    }
}
