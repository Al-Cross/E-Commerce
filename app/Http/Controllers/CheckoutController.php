<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Mail\OrderPlaced;
use App\Order;
use App\OrderProduct;
use App\Product;
use Cartalyst\Stripe\Exception\CardErrorException;
use Cartalyst\Stripe\Laravel\Facades\Stripe;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    /**
     * Display the page for the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $gateway = resolve('\Braintree\Gateway');

        $paypalToken = $gateway->clientToken()->generate();

        $orderDetails = collect([
            'tax' => $this->getNumbers()->get('newTax'),
            'toPay' => $this->getNumbers()->get('newTotal')
        ]);

        return view('products.checkout', compact('orderDetails', 'paypalToken'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CheckoutRequest $request)
    {
        try {
            $charges = Stripe::charges()->create([
                'amount' => round($this->getNumbers()->get('newTotal'), 2, PHP_ROUND_HALF_UP),
                'currency' => 'EUR',
                'description' => 'Order',
                'source' => $request->stripeToken,
                'receipt_email' => $request->email,
            ]);

            $order = $this->addToOrdersTables($request, null);
            Mail::send(new OrderPlaced($order));

            \Cart::clear();
            session()->forget('coupon');

            return view('products.thankyou');
        } catch (CardErrorException $e) {
            $this->addToOrdersTables($request, $e->getMessage());

            return back()->with('error', 'Error! '.$e->getMessage());
        }
    }

    /**
     * Process the PayPal payment.
     *
     * @return \Illuminate\Http\Response
     */
    public function paypal()
    {
        $gateway = resolve('\Braintree\Gateway');

        $nonce = request()->payment_method_nonce;

        $result = $gateway->transaction()->sale([
            'amount' => round($this->getNumbers()->get('newTotal'), 2, PHP_ROUND_HALF_UP),
            'paymentMethodNonce' => $nonce,
            'options' => [
                'submitForSettlement' => true
            ]
        ]);

        $transaction = $result->transaction;
        $email = $transaction->paypal['payerEmail'];
        $name = $transaction->paypal['payerFirstName'].' '.$transaction->paypal['payerLastName'];

        if ($result->success || ! is_null($result->transaction)) {
            $order = $this->addToOrdersTablesPaypal($name, $email, null);

            Mail::send(new OrderPlaced($order, $email));
            \Cart::clear();
            session()->forget('coupon');

            return view('products.thankyou');
        } else {
            $this->addToOrdersTablesPaypal($name, $email, $result->message);

            return back()->with(
                'flash',
                'An error occurred with the message: '.$result->message
            );
        }
    }

    /**
     * Save the order in the respective DB tables.
     *
     * @param  App\Http\Requests  $request
     * @param  CardException  $error
     * @return object
     */
    protected function addToOrdersTables($request, $error = null)
    {
        $order = Order::create([
            'user_id' => auth()->user() ? auth()->user()->id : null,
            'billing_name' => $request->name,
            'billing_email' => $request->email,
            'billing_address' => $request->address,
            'billing_city' => $request->city,
            'billing_postalcode' => $request->postalcode,
            'billing_country' => $request->country,
            'billing_phone' => $request->phone,
            'billing_name_on_card' => $request->name_on_card,
            'discount' => $this->getNumbers()->get('discount'),
            'billing_subtotal' => $this->getNumbers()->get('newSubtotal'),
            'billing_tax' => $this->getNumbers()->get('newTax'),
            'billing_total' => $this->getNumbers()->get('newTotal'),
            'error' => $error
        ]);

        $cart = \Cart::getContent();

        foreach ($cart as $item) {
            OrderProduct::create([
                'order_id' => $order->id,
                'product_id' => $item->id,
                'quantity' => $item->quantity
            ]);

            $product = Product::findOrFail($item->id);
            $product->reduceQuantity($item->quantity);
        }

        return $order;
    }

    /**
     * Save the order in the respective DB tables.
     *
     * @param  App\Http\Requests  $request
     * @param  CardException  $error
     */
    protected function addToOrdersTablesPaypal($name, $email, $error = null)
    {
        $order = Order::create([
            'user_id' => auth()->user() ? auth()->user()->id : null,
            'billing_name' => $name,
            'billing_email' => $email,
            'billing_subtotal' => $this->getNumbers()->get('newSubtotal'),
            'billing_tax' => $this->getNumbers()->get('newTax'),
            'billing_total' => $this->getNumbers()->get('newTotal'),
            'payment_gateway' => 'paypal',
            'discount' => $this->getNumbers()->get('discount'),
            'error' => $error
        ]);

        $cart = \Cart::getContent();

        foreach ($cart as $item) {
            OrderProduct::create([
                'order_id' => $order->id,
                'product_id' => $item->id,
                'quantity' => $item->quantity
            ]);

            $product = Product::findOrFail($item->id);
            $product->reduceQuantity($item->quantity);
        }

        return $order;
    }

    /**
     * Make the necessary calculations.
     *
     * @return Collection
     */
    private function getNumbers()
    {
        $tax = 0.2;
        $discount = session()->get('coupon')['discount'] ?? 0;
        $newSubTotal = \Cart::getSubtotal() - $discount;
        $newTax = $newSubTotal * $tax;
        $newTotal = $newSubTotal * (1 + $tax);

        return collect([
            'tax' => $tax,
            'newSubtotal' => $newSubTotal,
            'discount' => round($discount, 2, PHP_ROUND_HALF_UP),
            'newTax' => $newTax,
            'newTotal' => $newTotal
        ]);
    }
}
