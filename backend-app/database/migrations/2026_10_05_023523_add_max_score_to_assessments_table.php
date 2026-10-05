<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Questions are now editable, so each assessment records the maximum score it was taken against.
     */
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_score')->nullable();
        });

        // Earlier assessments: five symptoms scored 0-3, or sixteen questions scored 0-2.
        DB::table('assessments')
            ->whereNull('max_score')
            ->update(['max_score' => DB::raw('(select case when count(*) = 5 then 15 else count(*) * 2 end from symptoms where symptoms.assessment_id = assessments.id)')]);
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn('max_score');
        });
    }
};
