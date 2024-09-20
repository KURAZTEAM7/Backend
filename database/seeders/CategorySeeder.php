<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create(['name' => 'Animals & Pet Supplies']);
        Category::create(['name' => 'Arts & Entertainment']);
        Category::create(['name' => 'Baby & Toddler']);
        Category::create(['name' => 'Business & Industrial']);
        Category::create(['name' => 'Cameras & Optics']);
        Category::create(['name' => 'Clothing & Accessories']);
        Category::create(['name' => 'Electronics']);
        Category::create(['name' => 'Food, Beverages & Tobacco']);
        Category::create(['name' => 'Furniture']);
        Category::create(['name' => 'Hardware']);
        Category::create(['name' => 'Health & Beauty']);
        Category::create(['name' => 'Home & Garden']);
        Category::create(['name' => 'Luggage & Bags']);
        Category::create(['name' => 'Mature']);
        Category::create(['name' => 'Media']);
        Category::create(['name' => 'Office Supplies']);
        Category::create(['name' => 'Religious & Ceremonial']);
        Category::create(['name' => 'Software']);
        Category::create(['name' => 'Sporting Goods']);
        Category::create(['name' => 'Toys & Games']);
        Category::create(['name' => 'Vehicles & Parts']);
    }
}
