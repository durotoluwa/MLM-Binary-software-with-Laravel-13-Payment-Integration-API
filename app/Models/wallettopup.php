<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class wallettopup extends Model
{
       protected $table = 'wallettopup';
       protected $fillable = [
        'user_id', 'amount', 'payment_method', 'bank_name',
        'account_name', 'payment_proof', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
