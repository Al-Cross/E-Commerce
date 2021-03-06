@extends ('layouts.app')

@section ('title', 'My Order')

@section ('content')
    <section class="section-content padding-y">
        <div class="container">
            <div class="row">
                <aside class="col-md-3">
                    <ul class="list-group">
                        <a class="list-group-item" href="{{ route('profile') }}"> Account overview  </a>
                        <a class="list-group-item active" href="{{ route('orders.index') }}"> My Orders </a>
                        <a class="list-group-item" href="{{ route('wishlist', $order->user_id) }}"> My wishlist </a>
                        <a class="list-group-item" href="{{ route('edit.profile', $order->user_id) }}">Settings </a>
                    </ul>
                </aside>

                <section class="section-content bg padding-y w-75">
                    <div class="container">
						<article class="card  mb-3">
							<div class="card-body">
								<h5 class="card-title mb-4">Order: {{ $order->id }}</h5>

								<div class="row">
									<div class="col-md-5">
										@foreach ($products as $product)
											<div class="col-md-10">
												<figure class="itemside  mb-3">
													<div class="aside"><img src="{{ asset('storage/' . $product->mainImage()) }}" class="border img-sm"></div>
													<figcaption class="info">
														<div>
															<span class="font-weight-bold">Order Placed: </span>
														</div>
														<time class="text-muted mb-4"><i class="fa fa-calendar-alt"></i> {{ $order->created_at->format('d. M Y') }}</time>
														<p><a href="{{ $product->path() }}">{{ $product->name }}</a></p>
														<p>{{ $order->quantity }} </p>
													</figcaption>
												</figure>
											</div>
										@endforeach
									</div>

									<div class="col-md-6">
										<table class="table">
											<thead>
												<tr>
													<th style="white-space: nowrap;">Billing Name</th>
													<th>Address</th>
													<th>City</th>
													<th>Subtotal</th>
													<th>Total</th>
												</tr>
											</thead>
											<tbody>
												<tr>
													<td>{{ $order->billing_name }}</td>
													<td>{{ $order->billing_address }}</td>
													<td>{{ $order->billing_city }}</td>
													<td>{{ number_format($order->billing_subtotal, 2) }}</td>
													<td class="text-danger">{{ number_format($order->billing_total, 2) }}</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</article>
					</div>
				</section>
			</div>
		</div>
	</section>
@endsection
