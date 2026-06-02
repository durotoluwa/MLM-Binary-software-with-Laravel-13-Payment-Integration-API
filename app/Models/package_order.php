<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class package_order extends Model
{
  
       protected $table = 'package_order';
protected $fillable = [
'user_id',
'package_id',
'status',
 ];
    

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
    
    
    public function packageProductOrders()
    {
        return $this->hasMany(package_product_orders::class, 'package_order_id');
    }


    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

public function product()
{
    return $this->belongsTo(Product::class, 'product_id');
}

   
}
