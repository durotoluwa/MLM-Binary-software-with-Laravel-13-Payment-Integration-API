<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class incentive_settings extends Model
{
          protected $table = 'incentive_settings';
protected $fillable = [
'rank',
'required_ctp',
'min_lesser_leg_percent',
'required_downline_count',
'required_downline_rank',
'is_active'
 ];
}


 