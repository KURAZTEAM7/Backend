<?php

namespace Database\Seeders;

use App\Models\Product;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

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

        // Sample product categories, brands, and tags to make search functionality testing better
        $brands = ['Apple', 'Samsung', 'Sony', 'Dell', 'HP'];
        $sampleTags = [
            ['electronics', 'new', 'featured'],
            ['sale', 'popular', 'top-rated'],
            ['limited-edition', 'trending', 'bestseller'],
        ];

        foreach (range(1, 100) as $i) {
            $product = new Product;
            $product->title = $faker->unique()->word.' '.$faker->randomElement(['Phone', 'Laptop', 'Tablet', 'Camera', 'Headphones']);
            $product->description = $faker->sentence(10);
            $product->price = $faker->randomFloat(2, 50, 2000);
            $product->flexible_pricing = $faker->boolean();
            $product->brand = $faker->randomElement($brands);
            $product['model'] = strtoupper($faker->lexify('????-###'));
            $product->image_urls = [
                'https://random.imagecdn.app/500/500',
                'https://random.imagecdn.app/500/500',
                'https://random.imagecdn.app/500/500',
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
