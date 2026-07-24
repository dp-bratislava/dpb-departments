<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dpb_departments_department_groups', function (Blueprint $table) {
            $table->comment('Groups of departments');
            $table->id();
            $table->string('uri')
                ->nullable(false)
                ->comment('Unique URI to identify task item group in application layer')
                ->unique();
            $table->string('title');
            $table->text('description')
                ->nullable(true);
            $table->timestamps();
        });

        Schema::create('dpb_departments_department_group', function (Blueprint $table) {
            $table->comment('Pivot binding departments to groups');
            $table->foreignId('department_id')
                ->constrained('datahub_departments', 'id')
                ->cascadeOnDelete();
            $table->foreignId('group_id')
                ->constrained('dpb_departments_department_groups', 'id')
                ->cascadeOnDelete();

            $table->primary(['department_id', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dpb_departments_department_group');
        Schema::dropIfExists('dpb_departments_department_groups');
    }
};
