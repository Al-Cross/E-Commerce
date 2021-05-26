<?php

namespace App\Http\Controllers\Admin;

use App\AttributeValues;
use App\Category;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Images;
use App\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductUploadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::all();

        return view('admin.products.index', compact('products'));
    }

    /**
     * Display a page for creating a resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Category $category)
    {
        $categories = Category::all();

        if (request()->wantsJson()) {
            return $category->attributes()->with('values', 'values.product')->get();
        }

        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Http\Requests\StoreProductRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        $product = Product::create([
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'],
            'price' => $data['price'],
            'sale_price' => $data['sale_price'],
            'quantity' => $data['quantity'],
            'featured' => $data['featured']
        ]);

        isset($data['attr_value']) ? $attr_values['fromField'] = $data['attr_value'] : $attr_values['fromField'] = [];
        isset($data['select_value']) ? $attr_values['fromSelect'] = $data['select_value'] : $attr_values['fromSelect'] = [];

        $this->addAttributes($attr_values, $product);

        $this->saveImages($product->id, request()->file('image'));

        return response(['redirect' => '/products']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Product  $product
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $categories = Category::all();

        return view(
            'admin.products.edit',
            compact('product', 'categories')
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param App\Product  $product
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Product $product, StoreProductRequest $request)
    {
        $data = $request->validated();

        $product->update([
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'],
            'price' => $data['price'],
            'sale_price' => $data['sale_price'],
            'quantity' => $data['quantity'],
            'featured' => $data['featured']
        ]);

        isset($data['attr_value']) ? $attr_values['fromField'] = $data['attr_value'] : $attr_values['fromField'] = [];
        isset($data['select_value']) ? $attr_values['fromSelect'] = $data['select_value'] : $attr_values['fromSelect'] = [];

        $this->addAttributes($attr_values, $product);

        if ($request->has('image')) {
            $this->saveImages($product->id, $request->file('image'));
        }

        return response(['redirect' => $product->path()]);
    }

    /**
     * Delete the selected image.
     *
     * @param App\Product $product
     * @param int  $imageId
     */
    public function removeImage(Product $product, $imageId)
    {
        $image = $product->images->find($imageId);
        $imagePath = basename($image->path);

        Storage::disk('public')->delete('/images/'.$imagePath);
        $image->delete();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $images = $product->images;

        if (isset($images)) {
            foreach ($images as $image) {
                $imagePath[] = basename($image->path);
            }

            foreach ($imagePath as $path) {
                Storage::disk('public')->delete('/images/'.$path);
            }
        }

        $product->delete();

        return redirect()
            ->back()
            ->with('flash', 'Product successfully removed.');
    }

    /**
     * Store the images.
     *
     * @param array $images
     */
    public function saveImages($productId, $images = null)
    {
        $paths = [];

        foreach ($images as $image) {
            $imageName = $image->hashName();
            $paths[] = $image->storeAs('images', $imageName);
        }

        foreach ($paths as $path) {
            Images::create([
                'product_id' => $productId,
                'path' => $path
            ]);
        }
    }

    /**
     * Store the values of the product attributes.
     *
     * @param array $fromInputField
     * @param array $fromSelect
     * @param App\Product $product
     */
    public function addAttributes($input, $product)
    {
        $product->attributes()->detach();

        $filteredInput = array_filter($input['fromField'], function ($string) {
            return strpos($string, 'undefined') === false;
        });

        foreach ($filteredInput as $attribute => $value) {
            $attr_value = AttributeValues::firstOrCreate(
                ['value' => $value],
                ['attribute_id' => $attribute]
            );

            $product->attributes()->attach($attr_value->id);
        }

        foreach ($input['fromSelect'] as $value_id) {
            $product->attributes()->attach($value_id);
        }
    }
}
