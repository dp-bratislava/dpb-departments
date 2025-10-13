<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('dpb_departments_mm_departmentmeta', function (Blueprint $table) {

            $table->unsignedBigInteger('department_id')->index();
            $table->string('param_name', 100);
            $table->text('param_value')->nullable();

            $table->unique(['department_id', 'param_name'], 'dpb_dep_param_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dpb_departments_mm_departmentmeta');
    }
};
