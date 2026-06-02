<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
 


    public function edit()
        
    {
         $user = Auth::user(); 
        $settings = [
            'registration_fee' => Setting::getValue('registration_fee', 5000),
          //  'starter_bonus_1st' => Setting::getValue('starter_bonus_1st', 1820),
          //  'starter_bonus_2nd' => Setting::getValue('starter_bonus_2nd', 490),
          //  'starter_bonus_3rd' => Setting::getValue('starter_bonus_3rd', 350),
            //'starter_bonus_4th' => Setting::getValue('starter_bonus_4th', 140),
             'usd_conversion_rate' => Setting::getValue('usd_conversion_rate', 1600),
        ];

        return view('superadmin.settings.edit', compact('settings','user'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'registration_fee' => 'required|numeric|min:0',
           // 'starter_bonus_1st' => 'required|numeric|min:0',
           // 'starter_bonus_2nd' => 'required|numeric|min:0',
           // 'starter_bonus_3rd' => 'required|numeric|min:0',
            //'starter_bonus_4th' => 'required|numeric|min:0',
 'usd_conversion_rate' => 'required|numeric|min:0',
            
        ]);

        Setting::setValue('registration_fee', $request->registration_fee);
    
       // Setting::setValue('starter_bonus_1st', $request->starter_bonus_1st);
      //  Setting::setValue('starter_bonus_2nd', $request->starter_bonus_2nd);
      //  Setting::setValue('starter_bonus_3rd', $request->starter_bonus_3rd);
      //  Setting::setValue('starter_bonus_4th', $request->starter_bonus_4th);

        Setting::setValue('usd_conversion_rate', $request->usd_conversion_rate);

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
