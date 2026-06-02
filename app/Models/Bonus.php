<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bonus extends Model
{
    use HasFactory;
protected $table = 'bonus';
    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'description',
        'is_paid',
        'paid_at',
        'is_approved',
        'approved_at',
        'approved_by',
        'source_user_id',
        'transaction_id',
        'status',
    ];

   

    protected $casts = [
    'is_paid' => 'boolean',
    'paid_at' => 'datetime',
       'is_approved' => 'boolean',
    'approved_at' => 'datetime',
];

public function user()
{
    return $this->belongsTo(User::class);
}

public function receiver()
{
    return $this->belongsTo(User::class, 'user_id'); // sponsor/upline who received bonus
}

public function sourceUser()
{
    return $this->belongsTo(User::class, 'source_user_id'); // downline who triggered bonus
}



}
