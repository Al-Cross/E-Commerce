<?php

namespace App\Http\Controllers\Admin;

use App\Coupon;
use App\Http\Controllers\Controller;
use App\Images;
use App\Order;
use App\Product;
use App\User;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::where('email', '!=', config('e-commerce.administrators'))
            ->count();

        $products = Product::count();
        $images = Images::count();
        $orders = Order::count();

        return view('admin.index', compact('users', 'products', 'images', 'orders'));
    }

    /**
     * Display the specified resource.
     *
     * @param  App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $users = User::paginate(40);

        return view('admin.users', compact('users'));
    }

    /**
     * Display all closed orders.
     *
     * @return \Illuminate\Http\Response
     */
    public function orders()
    {
        $orders = Order::with('products')->paginate(40);
        $income = $orders->sum('billing_total');

        return view('admin.orders', compact('orders', 'income'));
    }

    /**
     * Find a user in the database.
     *
     * @return \Illuminate\Http\Response
     */
    public function search()
    {
        $search = request()->input('query');

        $users = User::search($search)->get();

        return view('admin.search', compact('users'));
    }

    /**
     * Remove the specified user from storage.
     *
     * @param App\User  $user
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()
            ->back()
            ->with(
                'flash',
                'The user has been successfully removed from the site.'
            );
    }

    /**
     * Display all registered coupons.
     *
     * @return \Illuminate\Http\Response
     */
    public function coupons()
    {
        $coupons = Coupon::all();

        return view('admin.coupons.all', compact('coupons'));
    }

    /**
     * Display the page for creating a coupon.
     *
     * @return \Illuminate\Http\Response
     */
    public function createCoupon()
    {
        return view('admin.coupons.new');
    }

    /**
     * Save a new coupon.
     *
     * @return \Illuminate\Http\Response
     */
    public function storeCoupon()
    {
        $validated = request()->validate(
            [
                'code' => ['required', 'string'],
                'type' => ['required', 'string', 'in:fixed,percent'],
                'value' => ['required_without:percent_off', 'numeric', 'nullable'],
                'percent_off' => ['required_without:value', 'numeric', 'nullable']
            ],
            [
                'value.required_without' => 'Please set a discount value.',
                'percent_off.required_without' => 'Please set a discount.'
            ]
        );

        Coupon::create([
            'code' => $validated['code'],
            'type' => $validated['type'],
            'value' => $validated['value'] ?? null,
            'percent_off' => $validated['percent_off'] ?? null
        ]);

        return redirect()
            ->back()
            ->with('flash', 'The coupon has been successfully registered!');
    }

    /**
     * Remove the coupon from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroyCoupon(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()
            ->back()
            ->with('flash', 'Coupon removed.');
    }
}
