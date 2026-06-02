<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class package_product_orders  extends Model
{
       protected $table = 'package_product_orders';
protected $fillable = [
'user_id',
'package_id',
'product_id',
'package_order_id',
'qty',
 ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

 

  public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function packageOrder()
    {
        return $this->belongsTo(package_orders::class, 'package_order_id');
    }
}
