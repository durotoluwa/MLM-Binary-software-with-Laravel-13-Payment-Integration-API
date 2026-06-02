<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class userpackage extends Model
{
    protected $table = 'userpackage'; 
        protected $fillable = [
        'total_amount_package','previous_package_id','acctName','bankName','amount','user_id', 'package_id', 'amount_paid', 'ctp_received', 'payment_method', 'status', 'payment_proof','approved_at', 'approved_by', 'is_approved','package_order_status'
    ];

    public function package() {
        return $this->belongsTo(Package::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function products() {
        return $this->belongsToMany(Product::class, 'package_product_orders', 'user_id', 'product_id');
    }
    public function packageOrders()
    {
        return $this->hasMany(package_product_orders::class, 'user_id');
    }

    public function packageOrder()
    {
        return $this->belongsTo(package_order::class, 'package_order_id');
    }
  

 

public function packageProductOrders()
{
    return $this->hasMany(\App\Models\package_product_orders::class, 'package_id', 'package_id')
        ->whereColumn('user_id', 'user_id'); // Matches user_id from both tables
}


 

    
}
