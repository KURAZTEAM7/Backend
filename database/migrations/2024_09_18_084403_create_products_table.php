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
        Schema::create('products', function (Blueprint $table) {
            $table->id('product_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('flexible_pricing')->default(false);
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->json('image_urls')->nullable(); // Storing images as JSON array
            $table->string('barcode_upc')->nullable();
            $table->string('barcode_eac')->nullable();
            $table->boolean('product_availability')->default(true);
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('category_id');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('company_id')->references('company_id')->on('companies');
            $table->foreign('category_id')->references('category_id')->on('categories');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
