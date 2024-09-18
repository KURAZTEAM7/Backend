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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');  // Foreign key to the user

            $table->string('store_name');  // Store name
            $table->string('phone_number');
            $table->string('email')->unique();
            $table->string('logo')->nullable();  // Store logo (optional)
            $table->string('logo_public_id')->nullable();  // Public ID for cloud storage (optional)

            // Location-related fields
            $table->string('zone');
            $table->string('region');
            $table->string('google_map_location')->nullable();  // Google map coordinates

            // Social media and contact information
            $table->string('website')->nullable();
            $table->string('telegram')->nullable();
            $table->string('whatsapp')->nullable();

            // Tax and licensing information
            $table->string('tin_number')->unique();
            $table->string('license');
            $table->string('license_public_id');    // Public ID for cloud storage

            $table->text('description')->nullable();  // Store bio/description (optional)

            // Approval status (enum)
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                ->default('pending');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
