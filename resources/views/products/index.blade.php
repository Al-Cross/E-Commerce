@extends ('layouts.app')

@section ('title', 'Products')

@section ('content')
	<section class="section-content bg padding-y">
	    <div class="container">
	        <div id="code_prod_complex">
	            <div class="row">
	                @forelse($products as $product)
	                    <div class="col-md-3">
	                        <figure class="card card-product-grid">
                                <div class="img-wrap padding-y">
                                	@if($product->featured)
	                                	<span class="badge badge-warning ml-5"> FEATURED </span>
                                	@endif
                                	@if($product->new())
	                                	<span class="badge badge-danger"> NEW </span>
                                	@endif
	                                <a class="title" href="{{ $product->path() }}">
	                                	<img src="{{ asset('storage/' . $product->mainImage()) }}"
	                                		style="height: 200px;"
	                                		alt="mainImage">
	                            </div>

			                            <figcaption class="info-wrap border-top">
			                                {{ $product->name }}
		                            </a>
	                            </figcaption>
	                            <div class="bottom-wrap">
		                            @if ($product->inStock)
		                            	<form action="{{ route('add-to-cart') }}" method="POST">
		                            		@csrf
		                            		<input type="hidden" name="id" value="{{ $product->id }}">
		                            		<input type="hidden" name="quantity" value="1">
		                            		<input type="hidden" name="price" value="{{ $product->price }}">
		                            		<button type="submit" class="btn btn-sm btn-success float-right">
			                                	<i class="fa fa-cart-arrow-down"></i> Buy Now
			                                </button>
		                            	</form>
	                            	@endif
	                                @if ($product->sale_price != 0)
	                                    <div class="price-wrap h5">
	                                        <span class="price h3"> {{ config('e-commerce.currency_symbol').$product->sale_price }} </span>
	                                        <del class="price-old text-danger"> {{ config('e-commerce.currency_symbol').$product->price }}</del>
	                                    </div>
	                                @else
	                                    <div class="price-wrap h5">
	                                        <span class="price h3"> {{ config('e-commerce.currency_symbol').$product->price }} </span>
	                                    </div>
	                                @endif
	                            </div>
	                        </figure>
	                    </div>
	                @empty
	                    <p>No Products found yet.</p>
	                @endforelse
	            </div>
	            @include('partials._errors')
	        </div>
	        {{ $products->links() }}
	    </div>
	</section>
@endsection
