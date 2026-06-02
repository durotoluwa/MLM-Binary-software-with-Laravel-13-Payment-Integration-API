<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class product_order_item extends Model
{
    protected $table = 'product_order_item';
    
    protected $fillable = [
        'product_order_id', 'product_id', 'product_name', 'quantity', 'price', 'apc', 'ctp'
    ];

    public function order()
    {
        return $this->belongsTo(product_order::class, 'product_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
