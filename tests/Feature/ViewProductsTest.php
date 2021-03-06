<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\OrderProduct;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewProductsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp() :void
    {
        parent::setUp();

        $this->product = create('App\Product', ['price' => 20]);
    }
    /**
     * @test
     */
    public function a_user_can_view_all_products()
    {
        $this->get('/products')->assertSee($this->product->name)
        ->assertSee($this->product->price);
    }
    /**
     * @test
     */
    public function a_user_can_view_a_single_product()
    {
        $image = create('App\Images', ['product_id' => $this->product->id]);
        $this->get($this->product->path())
            ->assertSee($this->product->name)
            ->assertSee($this->product->description);
    }
    /**
    * @test
    */
    public function a_user_can_see_product_availability()
    {
        $this->get($this->product->path());

        $this->assertTrue($this->product->inStock);

        $product2 = create('App\Product', ['quantity' => 0]);

        $this->get($product2->path());

        $this->assertFalse($product2->inStock);
    }
    /**
     * @test
     */
    public function a_user_can_filter_products_according_to_a_category()
    {
        $category = create('App\Category');
        $productNotInCategory = create('App\Product', ['name' => 'Some product']);
        $productInCategory = create('App\Product', ['category_id' => $category->id]);

        create('App\Images', ['product_id' => $productInCategory->id]);

        $this->get(route('category', $category->slug))
            ->assertSee($productInCategory->name)
            ->assertDontSee($productNotInCategory->name);
    }
    /**
     * @test
     */
    public function a_user_can_filter_products_according_their_price()
    {
        $product2 = create('App\Product', ['price' => 30]);
        $product3 = create('App\Product', ['price' => 50]);

        $response = $this->get('/products?price=desc');
        foreach ($response['products'] as $product) {
            $descending[] = $product['price'];
        }

        $this->assertEquals([50, 30, 20], $descending);

        $response = $this->get('/products?price=asc');
        foreach ($response['products'] as $product) {
            $ascending[] = $product['price'];
        }

        $this->assertEquals([20, 30, 50], $ascending);
    }
}
