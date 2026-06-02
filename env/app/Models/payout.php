<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class payout extends Model
{
       protected $table = 'payout';
protected $fillable = [
'user_id',
'amount',
'status',
'paystack_reference',
'type',
 ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
