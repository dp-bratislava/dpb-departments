<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $this->copyTableDataToDpbUtilsMetaAttributesTable();
        Schema::rename(from: 'dpb_departments_mm_departmentmeta', to: 'zzz__dpb_departments_mm_departmentmeta');
    }

    public function down(): void
    {
        Schema::rename(from: 'zzz__dpb_departments_mm_departmentmeta', to: 'dpb_departments_mm_departmentmeta');
    }

    private function copyTableDataToDpbUtilsMetaAttributesTable(): void
    {
        DB::table(table: 'dpb_utils_model_metaattribute')
            ->insertUsing(
                columns: [
                    'metaable_type', 'metaable_id', 'meta_key', 'meta_value', 'updated_at', 'created_at'
                ],
                query: DB::table(table: 'dpb_departments_mm_departmentmeta')
                    ->selectRaw(expression: "'Dpb\\\\Departments\\\\Models\\\\Department' AS `metaable_type`, `department_id` AS `metaable_id`, `param_name` AS `meta_key`, `param_value` AS `meta_value`, NOW() AS `updated_at`, NOW() AS `created_at`")
            );
    }
};
