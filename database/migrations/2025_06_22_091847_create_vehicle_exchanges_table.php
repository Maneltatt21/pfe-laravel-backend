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
        Schema::create('vehicle_exchanges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_driver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('to_driver_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->timestamp('request_date');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('before_photo_path')->nullable();
            $table->string('after_photo_path')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_exchanges');
    }
};
