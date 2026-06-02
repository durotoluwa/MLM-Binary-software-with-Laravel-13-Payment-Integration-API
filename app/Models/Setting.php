<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public $timestamps = true;

    public static function getValue($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function setValue($key, $value)
    {
        return static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Get USD conversion rate
     */
    public static function getUsdConversionRate()
    {
        return (float) static::getValue('usd_conversion_rate', 1500); // default 1 USD = ₦1500
    }

    /**
     * Set USD conversion rate
     */
    public static function setUsdConversionRate($rate)
    {
        return static::setValue('usd_conversion_rate', $rate);
    }
}
