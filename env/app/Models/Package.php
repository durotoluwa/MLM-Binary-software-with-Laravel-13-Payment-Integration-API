<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{

    
   protected $table = 'package';
protected $fillable = [
'package_image',
'packageName',
'description',
'price',
'bottle',
'cpts',
'status',
'apc',
 ];

    public function userpackage()
    {
        return $this->hasMany(userpackage::class, 'package_id');
    }
    public function packageOrders()
    {
        return $this->hasMany(package_product_orders::class, 'package_id');
    }
    public function products()
    {
return $this->belongsToMany(Product::class, 'package_product_orders', 'package_id', 'product_id');
    }
    public function packageOrdersMain()
    {
        return $this->hasMany(package_order::class, 'package_id');
    }

}
