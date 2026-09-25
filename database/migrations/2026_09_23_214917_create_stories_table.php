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
        Schema::create('stories', function (Blueprint $table) {
            $table->id();

            $table->string('source', 20)->index();
            $table->string('type', 20)->index();
            $table->string('status', 20)->default('pending')->index();

            $table->string('request_text', 255);
            $table->text('result_text')->nullable();
            $table->json('child_data')->nullable();

            $table->nullableMorphs('storyable');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
