<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $primaryKey = 'product_id';

    protected $fillable = [
        'title',
        'description',
        'price',
        'flexible_pricing',
        'brand',
        'model',
        'image_urls',
        'barcode_upc',
        'barcode_eac',
        'product_availability',
        'tags',
        'company_id',
        'category_id',
    ];

    protected $casts = [
        'image_urls' => 'array', // Cast JSON field
        'tags' => 'array',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
