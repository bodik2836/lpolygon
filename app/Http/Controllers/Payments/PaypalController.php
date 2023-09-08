<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
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

        return view('payments.paypal.checkout', compact('data', 'product'));
    }

    public function status(string $refId = '')
    {
        if (empty($refId))
            return redirect()->route('payments.paypal.checkout');

        $transInfo['status'] = 'error';
        $transInfo['msg'] = 'Transaction has been failed!';

        $payment_txn_id = base64_decode($refId);
        $transaction = Transaction::query()->where('transaction_id', $payment_txn_id)->first();

        if ($transaction) {
            $transInfo['status'] = 'success';
            $transInfo['ref_id'] = $refId;
            $transInfo['msg'] = 'Your Payment has been Successful!';
        }

        return view('payments.paypal.status', compact('transInfo', 'transaction'));
    }

    public function checkoutValidate()
    {
        $r = $this->paypalCheckout->checkoutValidate();

        return response($r);
    }
}
