<?php

namespace App\Http\Controllers;

class CartController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $cartContents = \Cart::getContent();

        $purchased = null;
        if ($user = auth()->user()) {
            $purchased = $user->orders()->with('products')->get();
        }

        return view('products.cart', compact('cartContents', 'purchased'));
    }

    /**
     * Update the contents of the cart.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        \Cart::update($id, ['quantity' => [
            'relative' => false,
            'value' => request()->quantity
        ]]);

        session()->flash('flash', 'Quantity was updated successfully.');

        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified item from the cart.
     *
     * @param int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (\Cart::isEmpty()) {
            return redirect('/');
        }

        \Cart::remove($id);

        return redirect()
            ->back()
            ->with('flash', 'Item removed from cart successfully.');
    }

    /**
     * Remove all items from the cart.
     *
     * @return \Illuminate\Http\Response
     */
    public function clearCart()
    {
        \Cart::clear();

        return redirect('/products');
    }
}
