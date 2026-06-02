<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;



class product_order extends Model
{
   
use HasFactory;
protected $table = 'product_order';
protected $fillable = [
'user_id',
'product_id',
'ordered_by',
'amount',
'payment_method',
'status',
'payment_proof',
'approved_at',
'apc',
'ctp',
'order_no',
'bank_name',
'account_name',
'amount_paid',
'proof',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

  
   

    public function items()
{
    return $this->hasMany(product_order_item::class, 'product_order_id');
}


 public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    
}
