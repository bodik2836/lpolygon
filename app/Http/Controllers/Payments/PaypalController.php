<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Services\Payments\Paypal\PaypalCheckout;

class PaypalController extends Controller
{
    public function __construct(
        protected PaypalCheckout $paypalCheckout
    ) {}

    public function index()
    {
        $data = [
            'paypal_client_id' => env('PAYPAL_CLIENT_ID')
        ];

        $product = [
            'sku' => 'DP123',
            'name' => 'Demo Product',
            'price' => 10,
            'currency' => 'USD'
        ];

        return view('payments.paypal', compact('data', 'product'));
    }

    public function checkoutValidate()
    {
        $r = $this->paypalCheckout->checkoutValidate();

        return response($r);
    }
}
