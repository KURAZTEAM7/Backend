<?php

namespace Database\Seeders;

use App\Models\Product;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Assuming you have vendor_id and category_id data seeded
        $vendorIds = \App\Models\Vendor::pluck('id')->toArray();
        $categoryIds = \App\Models\Category::pluck('id')->toArray();

        // Fetch random brands from an external API (DummyJSON)
        $brandResponse = Http::get('https://dummyjson.com/products');
        $brands = $brandResponse->ok() ? collect($brandResponse->json()['products'])->pluck('brand')->unique()->toArray() :
            ['Apple', 'Samsung', 'Sony', 'Dell', 'HP', 'Asus', 'Lenovo', 'Google', 'Microsoft', 'Xiaomi'];

        // Sample product categories and tags to make search functionality testing better
        $sampleTags = [
            ['electronics', 'new', 'featured'],
            ['sale', 'popular', 'top-rated'],
            ['limited-edition', 'trending', 'bestseller'],
            ['eco-friendly', 'discount', 'innovative'],
        ];

        foreach (range(1, 100) as $i) {
            $product = new Product;

            // Use real product names if API call succeeds
            $productResponse = Http::get('https://dummyjson.com/products');
            if ($productResponse->ok()) {
                $randomProduct = $productResponse->json()['products'][$faker->numberBetween(0, 29)]; // API provides 30 products
                $product->title = $randomProduct['title'];
                $product->description = $randomProduct['description'];
            } else {
                // Fallback to Faker-generated data
                $product->title = $faker->unique()->word . ' ' . $faker->randomElement(['Phone', 'Laptop', 'Tablet', 'Camera', 'Headphones']);
                $product->description = $faker->sentence(10);
            }

            $product->price = $faker->randomFloat(2, 50, 2000);
            $product->flexible_pricing = $faker->boolean();
            $product->brand = $faker->randomElement($brands);
            $product['model'] = strtoupper($faker->lexify('????-###'));
            $product->image_urls = [
                $faker->imageUrl(500, 500, 'electronics', true),
                $faker->imageUrl(500, 500, 'electronics', true),
                $faker->imageUrl(500, 500, 'electronics', true),
            ];
            $product->barcode_upc = $faker->ean13;
            $product->barcode_eac = $faker->ean8;
            $product->remaining_stock = $faker->numberBetween(10, 500);
            $product->tags = $faker->randomElement($sampleTags);
            $product->vendor_id = $faker->randomElement($vendorIds);
            $product->category_id = $faker->randomElement($categoryIds);
            $product->save();
        }
    }
}
