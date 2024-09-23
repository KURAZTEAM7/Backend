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
            $table->integer('remaining_stock');
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('category_id');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
