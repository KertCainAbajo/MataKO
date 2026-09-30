<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('symptoms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->string('symptom_name', 60);
            $table->unsignedTinyInteger('value');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['assessment_id', 'symptom_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('symptoms');
    }
};
