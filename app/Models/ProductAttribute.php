<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    protected $fillable = [
        'product_sku_id',
        'attribute_id',
        'attribute_value_id',
        'value_text',
    ];

    public function productSku()
    {
        return $this->belongsTo(ProductSku::class);
    }

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function attributeValue()
    {
        return $this->belongsTo(AttributeValue::class);
    }
}
