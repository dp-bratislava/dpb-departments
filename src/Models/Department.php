<?php

namespace Dpb\Departments\Models;

use Dpb\DatahubSync\Models\Department as DatahubDepartment;
use Dpb\DpbUtils\Concerns\HasModelMetaAttributes;

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
}
