<?php

namespace App\Http\Controllers;

use App\Coupon;

class CouponController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $coupon = Coupon::findByCode(request()->code);

        if (! $coupon) {
            return back()->with('flash', 'Invalid coupon code.');
        }

        session()->put('coupon', [
            'name' => $coupon->code,
            'discount' => $coupon->discount(\Cart::getSubtotal())
        ]);

        return back()->with('flash', 'Coupon has been applied!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        session()->forget('coupon');

        return back()->with('flash', 'The coupon has been removed.');
    }
}
