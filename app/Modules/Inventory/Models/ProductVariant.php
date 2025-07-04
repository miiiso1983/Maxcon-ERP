<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'barcode',
        'price',
        'stock',
        // Add other fields as needed
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
