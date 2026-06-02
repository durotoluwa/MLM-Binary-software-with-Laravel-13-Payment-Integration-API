<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class incentives extends Model
{
         protected $table = 'incentives';
protected $fillable = [
'user_id',
'rank',
'incentive_name',
'status',
'achieved_at'
 ];

 protected $casts = [
    'achieved_at' => 'datetime',
];


 
}


    
