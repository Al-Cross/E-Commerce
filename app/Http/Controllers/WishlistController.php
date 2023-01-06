<?php

namespace App\Http\Controllers;

use App\User;
use App\Wishlist;

class WishlistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(User $user)
    {
        $wishlist = Wishlist::where('user_id', '=', $user->id)
            ->orderBy('id', 'desc')
            ->paginate(20);

        $products = $wishlist->load(['product', 'product.images']);

        if (request()->wantsJson()) {
            return $products;
        }

        return view('users.wishlist', compact('wishlist', 'user'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int  $userId
     * @return \Illuminate\Http\Response
     */
    public function store($userId)
    {
        Wishlist::create([
            'user_id' => $userId,
            'product_id' => request()->productId
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $userId
     * @param  int  $productId
     * @return \Illuminate\Http\Response
     */
    public function destroy($userId, $productId)
    {
        $item = Wishlist::where('product_id', '=', $productId)->first();

        $item->delete();

        return back()
            ->with('flash', 'Removed successfully from the wishlist.');
    }
}
