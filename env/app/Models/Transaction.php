<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    // Fix: explicitly set the correct table name
    protected $table = 'transaction';

    protected $fillable = [
        'user_id', 'type', 'method', 'status', 'proof',
        'sendername', 'bank_name', 'transaction_no'
    ];

    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}
}