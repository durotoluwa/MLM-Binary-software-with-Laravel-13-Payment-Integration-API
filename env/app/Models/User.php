<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Lab404\Impersonate\Models\Impersonate;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;



class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;
    use Impersonate;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'username', 'password', 'withdrawal_fee', 'amount_payable','userreg_id',
    'upline_username', 'sponsor_username', 'status', 'downline_cpt',
        'parent_id', 'sponsor_id', 'position', 'downline_left_cpt', 'downline_right_cpt',
        'kin_name', 'kin_phone', 'kin_email', 'kin_address','user_rank',
        'bank_name', 'account_no', 'account_name', 'bank_code', 'payment_status', 'transaction_pin', 'profile_photo_path',
        'is_muted','total_ctp','withdraw_wallet_balance','left_ctp_for_matching','right_ctp_for_matching',
        'user_plan','unilevel_wallet_balance','deposit_wallet_balance','address',
         'city', 'state', 'country','impersonation_token','impersonation_token_expires_at','current_c_cpts','p_c_cpts','current_p_c_cpts'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Use username for authentication instead of email.
     */
   // public function getAuthIdentifierName()
   // {
      //  return 'username';
   // }

   public function getAuthIdentifierName()
{
    return 'id'; // default is 'id'
}


    public function upline()
{
    return $this->belongsTo(User::class, 'upline_username', 'username');
}

public function transactions()
{
    return $this->hasMany(Transaction::class, 'user_id');
}
public function wallettopup()
{
    return $this->hasMany(wallettopup::class, 'user_id');
}

public function withdrawal()
{
    return $this->hasMany(withdrawal::class, 'user_id');
}

public function payout()
{
    return $this->hasMany(payout::class, 'user_id');
}



public function packageProductOrders()
{
    return $this->hasMany(package_product_orders::class, 'user_id');
}

public function packageOrders()
{
    return $this->hasMany(package_order::class, 'user_id');
}

public function userPackages()
{
    return $this->hasMany(userpackage::class, 'user_id');
}


   
  public function canImpersonate()
    {
        return $this->hasRole('superadmin'); // Only superadmins
    }

    public function canBeImpersonated()
    {
        return !$this->hasRole('superadmin'); // Prevent impersonating other superadmins
    }


public function getProfileImageAttribute()
{
    return $this->profile_photo_path
        ? asset('user/' . $this->profile_photo_path)
        : asset('images/favicon.png');
}

public function sponsor()
{
    return $this->belongsTo(User::class, 'sponsor_id');
}


public function leftChild()
{
    return $this->hasOne(User::class, 'parent_id')->where('position', 'left');
}

public function rightChild()
{
    return $this->hasOne(User::class, 'parent_id')->where('position', 'right');
}


 

public function sponsorChildren()
{
    return $this->hasMany(User::class, 'sponsor_id');
}



public function sponsoruser()
{
    return $this->belongsTo(User::class, 'sponsor_id');
}

public function uplineuser()
{
    return $this->belongsTo(User::class, 'parent_id');
}


public function productOrders()
{
    return $this->hasMany(product_order::class, 'user_id');
}

 
public function incentives()
{
    return $this->hasMany(Incentive::class);
}
 
protected $primaryKey = 'id';


}

