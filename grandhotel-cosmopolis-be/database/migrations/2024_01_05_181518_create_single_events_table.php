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
            $table->string('guid')->index();
            $table->string('title_de');
            $table->string('title_en');
            $table->string('description_de');
            $table->string('description_en');
            $table->timestamp('start');
            $table->timestamp('end');
            $table->unsignedBigInteger('event_location_id')->index();
            $table->unsignedBigInteger('file_upload_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->timestamps();

            $table->foreign('event_location_id')->references('id')->on('event_locations')->cascadeOnDelete();
            $table->foreign('file_upload_id')->references('id')->on('file_uploads')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
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
