<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class product extends Model
{
protected $table = 'product';
protected $fillable = [
'product_image',
'productName',
'description',
'price',
'apc',
'cpts',
'status',
 ];

    public function orders()
    {
        return $this->hasMany(product_order::class, 'product_id');
    }

    public function orderItems()
    {
        return $this->hasMany(product_order_item::class, 'product_id');
    }
    public function packageOrders()
    {
        return $this->hasMany(package_product_orders::class, 'product_id');
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'product_order_item', 'product_id', 'user_id');
    }

}
