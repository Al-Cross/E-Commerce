<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.categories');
    }

    /**
     * Display a page for creating a resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $data = request()->validate([
            'name' => 'required',
            'attribute' => ['required', 'array', 'min:1'],
            'attribute.*' => 'required'
        ]);

        $category = Category::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name'])
        ]);

        \Cache::forget('categories');

        foreach ($data['attribute'] as $attribute) {
            $category->attributes()->create(['name' => $attribute]);
        }

        return redirect()->route('admin.dashboard.categories');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $category->delete();

        \Cache::forget('categories');
    }
}
