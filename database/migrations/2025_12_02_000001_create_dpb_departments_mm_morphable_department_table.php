<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create(
            table: 'dpb_departments_mm_morphable_department',
            callback: function (
                Blueprint $table
            ): void {
                $table->id();

                $table->foreignId(column: 'department_id')
                    ->constrained(table: 'datahub_departments')
                    ->cascadeOnDelete();

                $table->unsignedBigInteger(column: 'morphable_id');
                $table->string(column: 'morphable_type');

                $table->timestamp(column: 'created_at')->nullable()->useCurrent();

                $table->unique(columns: ['department_id', 'morphable_id', 'morphable_type'], name: 'unique_department_morphable');
                $table->index(columns: ['morphable_id', 'morphable_type'], name: 'idx_morphable');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(table: 'dpb_departments_mm_morphable_department');
    }
};
