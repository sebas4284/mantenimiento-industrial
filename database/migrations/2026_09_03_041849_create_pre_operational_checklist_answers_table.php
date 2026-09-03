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
        Schema::create('pre_operational_checklist_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('pre_operational_checklists')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('pre_operational_items')->cascadeOnDelete();
            $table->string('answer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_operational_checklist_answers');
    }
};
