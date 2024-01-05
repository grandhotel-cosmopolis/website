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
        Schema::create('single_events', function (Blueprint $table) {
            $table->id();
            $table->string('title_de');
            $table->string('title_en');
            $table->string('description_de');
            $table->string('description_en');
            $table->timestamp('start');
            $table->timestamp('end');
            $table->string('image_url')->nullable();
            $table->unsignedBigInteger('event_location_id')->index();
            $table->timestamps();

            $table->foreign('event_location_id')->references('id')->on('event_locations')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('single_events');
    }
};
