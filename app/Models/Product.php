<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'vendor_id',
        'category_id',
    ];

    protected $casts = [
        'image_urls' => 'array', // Cast JSON field
        'tags' => 'array',
    ];

    // Relationships
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
