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
        Schema::create('single_event_exceptions', function (Blueprint $table) {
            $table->id();
            $table->timestamp('start')->nullable();
            $table->timestamp('end')->nullable();
            $table->boolean('cancelled')->nullable();
            $table->unsignedBigInteger('single_event_id')->index();
            $table->unsignedBigInteger('event_location_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('single_event_id')->references('id')->on('single_events')->cascadeOnDelete();
            $table->foreign('event_location_id')->references('id')->on('event_locations')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('single_event_exceptions');
    }
};
