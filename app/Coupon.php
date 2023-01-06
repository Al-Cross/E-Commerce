<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $guarded = [];

    /**
     * Fetch the coupon by its code.
     *
     * @param  string  $code
     * @return object
     */
    public static function findByCode($code)
    {
        return self::where('code', $code)->first();
    }

    /**
     * Find the value of the discount according to its type.
     *
     * @param  int  $total
     * @return int
     */
    public function discount($total)
    {
        if ($this->type == 'fixed') {
            return $this->value;
        } elseif ($this->type == 'percent_off') {
            return ($this->percent_off / 100) * $total;
        } else {
            return 0;
        }
    }
}
