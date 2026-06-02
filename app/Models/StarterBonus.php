<?php

 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StarterBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',      // The upline who earned the bonus
        'from_user_id', // The user whose registration triggered the bonus
        'generation',   // 1 to 4
        'amount',
    ];

    /**
     * Upline user who receives the bonus.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The user who registered and triggered the bonus (the referral/child).
     */
    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }
}
