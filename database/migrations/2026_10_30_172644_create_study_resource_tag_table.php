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
        Schema::create('study_resource_tag', function (Blueprint $table) {
            $table->foreignId('study_resource_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->index()->constrained()->cascadeOnDelete();

            // The pair is the key, so the same tag can't be attached twice.
            // It is sorted by study_resource_id first, so it can't serve
            // "find by tag_id" lookups; the separate index above does that.
            $table->primary(['study_resource_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_resource_tag');
    }
};
