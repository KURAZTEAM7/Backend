<?php

namespace App\Services\Product;

use App\Models\Product;

class ProductHelper
{
    public static function levenshtein_search(string $query, string $property1, $property2 = 'id'): array
    {
        $allProducts = Product::whereNotNull($property1)
            ->orWhereNotNull($property2)
            ->get();

        $maxDistance = 4; // maximum distance for ignoring totally unrelated items
        $productsWithDistance = [];

        // filter items based on Levenshtein distance
        foreach ($allProducts as $product) {
            $dist1 = levenshtein($query, $product->$property1);
            $dist2 = levenshtein($query, $product->$property2);
            $levenshteinDistance = min($dist1, $dist2);

            if ($levenshteinDistance <= $maxDistance) {
                $productsWithDistance[] = [
                    'product' => $product,
                    'distance' => $levenshteinDistance,
                ];
            }
        }

        // sort by Levenshtein distance
        usort($productsWithDistance, function ($a, $b): int {
            return $a['distance'] <=> $b['distance'];
        });

        // extract the products
        return array_map(function ($item) {
            return $item['product'];
        }, $productsWithDistance);
    }

    public static function findSimilar($product)
    {
        $products = Product::where('category_id', $product->category_id)
            ->whereNot('product_id', $product->product_id)
            ->where(function ($query) use ($product) {
                $query->orWhereRaw('LOWER(tags) like ?', ['%'.strtolower($product->tags).'%'])
                    ->orWhereRaw('LOWER(title) like ?', ['%'.strtolower($product->title).'%'])
                    ->orWhereRaw('LOWER(description) like ?', ['%'.strtolower($product->description).'%'])
                    ->orWhereRaw('LOWER(model) like ?', ['%'.strtolower($product->model).'%'])
                    ->orWhereRaw('LOWER(brand) like ?', ['%'.strtolower($product->brand).'%'])
                    ->limit(10);
            })->get();

        return $products;
    }
}
