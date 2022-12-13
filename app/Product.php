<?php

namespace App;

use App\Notifications\AlmostOutOfStockNotification;
use App\Notifications\OutOfStockNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;

class Product extends Model
{
    use SearchableTrait;

    /**
     * Searchable rules.
     *
     * @var array
     */
    protected $searchable = [
        /**
         * Columns and their priority in search results.
         * Columns with higher values are more important.
         * Columns with equal values have equal importance.
         *
         * @var array
         */
        'columns' => [
            'products.name' => 10,
            'products.description' => 5
        ]
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'price', 'sale_price', 'quantity', 'featured'
    ];

    protected $casts = ['featured' => 'boolean'];

    /**
     * The URL to the resource.
     *
     * @return string
     */
    public function path()
    {
        return "/products/{$this->category->slug}/{$this->slug}";
    }

    /**
     * Define the relationship with App\Category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Define the relationship with App\Images.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function images()
    {
        return $this->hasMany(Images::class);
    }

    /**
     * Define the relationship with App\AttributeValues.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function attributes()
    {
        return $this->belongsToMany(
            AttributeValues::class,
            'product_attributes',
            'product_id',
            'attr_value_id'
        )->withTimestamps();
    }

    /**
     * Define the relationship with App\Wishlist.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Get the path to the main product image.
     *
     * @return string
     */
    public function mainImage()
    {
        if ($this->images->isEmpty()) {
            return '/images/default.jpeg';
        }

        return $this->images->where('product_id', $this->id)->first()->path;
    }

    /**
     * Add the product to the card.
     *
     * @param  Illuminate\Http\Request  $price
     * @param  Illuminate\Http\Request  $quantity
     */
    public function addToCart($price, $quantity)
    {
        \Cart::add($this->id, $this->name, $price, $quantity, $this->mainImage(), $this->quantity);
    }

    /**
     * Subtract the ordered quantity from the available quantity.
     *
     * @param $orderedQty
     */
    public function reduceQuantity($orderedQty)
    {
        $this->update(['quantity' => $this->quantity - $orderedQty]);

        $admin = resolve('App\User');

        if ($this->quantity >= 1 && $this->quantity <= 3) {
            $admin->notify(new AlmostOutOfStockNotification($this));
        } elseif ($this->quantity == 0) {
            $admin->notify(new OutOfStockNotification($this));
        }
    }

    /**
     * Apply the selected filters to the query.
     *
     * @param  Eloquent  $query
     * @param  App\Filters\ProductFilters  $filters
     * @return Eloquent query
     */
    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);
    }

    /**
     * Check the product availability.
     *
     * @return bool
     */
    public function getInStockAttribute()
    {
        if ($this->quantity == 0) {
            return false;
        }

        return true;
    }

    /**
     * Check if the product is newly added.
     *
     * @return bool
     */
    public function new()
    {
        return $this->created_at > Carbon::now()->subDays(7);
    }
}
