<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class withdrawal extends Model
{
     protected $table = 'withdrawal';
    protected $fillable = ['user_id', 'amount', 'status'];


   
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
