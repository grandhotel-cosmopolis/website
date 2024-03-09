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
            $table->string('title_de')->nullable();
            $table->string('title_en')->nullable();
            $table->longText('description_de')->nullable();
            $table->longText('description_en')->nullable();
            $table->timestamp('start')->nullable();
            $table->timestamp('end')->nullable();
            $table->unsignedBigInteger('single_event_id')->index();
            $table->unsignedBigInteger('event_location_id')->nullable()->index();
            $table->unsignedBigInteger('file_upload_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('single_event_id')->references('id')->on('single_events')->cascadeOnDelete();
            $table->foreign('event_location_id')->references('id')->on('event_locations')->cascadeOnDelete();
            $table->foreign('file_upload_id')->references('id')->on('file_uploads')->cascadeOnDelete();
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
