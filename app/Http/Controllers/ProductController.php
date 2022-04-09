<?php

namespace App\Http\Controllers;

use App\Category;
use App\Filters\ProductFilters;
use App\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Category $category, ProductFilters $filters)
    {
        if ($category->exists) {
            $products = $category->products()
                ->filter($filters)
                ->orderBy('featured', 'desc')
                ->latest()
                ->paginate(20);
        } else {
            $products = Product::filter($filters)
                ->orderBy('featured', 'desc')
                ->latest()
                ->paginate(20);
        }

        return view('products.index', compact('products'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category, Product $product)
    {
        return view('products.show', compact('category', 'product'));
    }

    /**
     * Add a product to the shopping cart.
     *
     * @return \Illuminate\Http\Response
     */
    public function fillCart()
    {
        $product = Product::findOrFail(request()->id);

        $product->addToCart(request('price'), request('quantity'));

        return redirect()
            ->back()
            ->with('flash', 'Item added to cart successfully.');
    }

    /**
     * Display a listing of search results.
     *
     * @return \Illuminate\Http\Response
     */
    public function search()
    {
        request()->validate([
            'query' => ['required', 'min:3']
        ]);

        $query = request()->input('query');

        $products = Product::search($query)->paginate(20);

        return view('products.search_results', compact('products'));
    }
}
