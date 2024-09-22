<?php

namespace App\Services\Product;

use App\Models\Product;

class ProductHelper
{
    public static function levenshtein_search($query, $property1, $property2 = 'id'): array
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
        usort($productsWithDistance, function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        // extract the products
        return array_map(function ($item) {
            return $item['product'];
        }, $productsWithDistance);
    }
}
