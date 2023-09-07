<?php

namespace App\Services\Payments\Paypal;

use App\Models\Transaction;
use GuzzleHttp\Client;
use \Exception;

class PaypalCheckout
{
    public $paypalAuthAPI = 'https://api-m.sandbox.paypal.com/v1/oauth2/token';
    public $paypalAPI = 'https://api-m.sandbox.paypal.com/v2/checkout/';
    private $paypalClientID = '';
    private $paypalSecret = '';

    public function __construct()
    {
        $this->paypalClientID = env('PAYPAL_CLIENT_ID');
        $this->paypalSecret = env('PAYPAL_CLIENT_SECRET');
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function validate($orderId)
    {
        $client = new Client(['base_uri' => $this->paypalAuthAPI]);
        $auth_response = $client->request('POST', '', [
            'auth' => [$this->paypalClientID, $this->paypalSecret],
            'form_params' => [
                'grant_type' => 'client_credentials',
            ],
        ]);
        $authData = json_decode($auth_response->getBody()->getContents());

        $http_code = $auth_response->getStatusCode(); // curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code != 200 && $authData->status == 'error') {
            throw new Exception('Error ' . $authData->status . ': ' . $authData->message);
        }

        if (empty($authData)) {
            return false;
        } else {
            if (!empty($authData->access_token)) {
                $client = new Client([
                    'base_uri' => $this->paypalAPI,
                ]);
                $response = $client->request('GET', 'orders/' . $orderId, [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $authData->access_token,
                    ],
                ]);


                $apiData = json_decode($response->getBody()->getContents());
                $http_code = $response->getStatusCode(); // curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($http_code != 200 && !empty($apiData->error)) {
                    throw new Exception('Error '.$apiData['error'].': '.$apiData['error_description']);
                }

                return !empty($apiData) && $http_code == 200 ? $apiData : false;
            } else {
                return false;
            }
        }
    }

    public function checkoutValidate()
    {
        $response = array('status' => 0, 'msg' => 'Transaction Failed!');
        if(!empty($_POST['paypal_order_check']) && !empty($_POST['order_id'])){
            // Validate and get order details with PayPal API
            try {
                $order = $this->validate($_POST['order_id']);
            } catch(Exception $e) {
                $api_error = $e->getMessage();
            }

            $transactionData = [];

            if(!empty($order)){
                $transactionData['order_id'] = $order->id;
                $transactionData['order_status'] = $order->status;

                if(!empty($order->purchase_units[0])){
                    $purchase_unit = $order->purchase_units[0];

                    $transactionData['item_number'] = $purchase_unit->custom_id;
                    $transactionData['item_name'] = $purchase_unit->description;

                    if(!empty($purchase_unit->amount)){
                        $transactionData['currency_code'] = $purchase_unit->amount->currency_code;
                        $transactionData['amount_value'] = $purchase_unit->amount->value;
                    }

                    if(!empty($purchase_unit->payments->captures[0])){
                        $payment_capture = $purchase_unit->payments->captures[0];
                        $transactionData['transaction_id'] = $payment_capture->id;
                        $transactionData['payment_status'] = $payment_capture->status;
                    }

                    if(!empty($purchase_unit->payee)){
                        $payee = $purchase_unit->payee;
                        $transactionData['payee_email_address'] = $payee->email_address;
                        $transactionData['merchant_id'] = $payee->merchant_id;
                    }
                }

                $payment_source = '';
                if(!empty($order->payment_source)){
                    foreach($order->payment_source as $key => $value){
                        $payment_source = $key;
                    }
                }

                if(!empty($order->payer)){
                    $payer = $order->payer;
                    $transactionData['payer_id'] = $payer->payer_id;
                    $payer_name = $payer->name;
                    $payer_given_name = !empty($payer_name->given_name) ? $payer_name->given_name : '';
                    $payer_surname = !empty($payer_name->surname) ? $payer_name->surname : '';
                    $payer_full_name = trim($payer_given_name.' '.$payer_surname);
                    $transactionData['payer_full_name'] = filter_var($payer_full_name, FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_HIGH);

                    $transactionData['payer_email_address'] = $payer->email_address;
                    $payer_address = $payer->address;
                    $transactionData['payer_country_code'] = !empty($payer_address->country_code) ? $payer_address->country_code : '';
                }

                if(!empty($transactionData['order_id']) && $transactionData['order_status'] == 'COMPLETED'){
                    $transaction = Transaction::query()->find($transactionData['transaction_id']);
                    $row_id = $transaction?->id;

                    $payment_id = 0;
                    if (!empty($row_id)) {
                        $payment_id = $row_id;
                    } else {
                        // Insert transaction data into the database
                        $transaction = new Transaction([
                            'item_sku' => $transactionData['item_number'],
                            'item_name' => $transactionData['item_name'],
                            'item_price' => $transactionData['amount_value'],
                            'item_price_currency' => $transactionData['currency_code'],
                            'payer_id' => $transactionData['payer_id'],
                            'payer_name' => $transactionData['payer_full_name'],
                            'payer_email' => $transactionData['payer_email_address'],
                            'payer_country' => $transactionData['payer_country_code'],
                            'merchant_id' => $transactionData['merchant_id'],
                            'merchant_email' => $transactionData['payee_email_address'],
                            'order_id' => $transactionData['order_id'],
                            'transaction_id' => $transactionData['transaction_id'],
                            'paid_amount' => $transactionData['amount_value'],
                            'paid_amount_currency' => $transactionData['currency_code'],
                            'payment_source' => $payment_source,
                            'payment_status' => $transactionData['payment_status'],
                        ]);

                        if($transaction->save()){
                            $payment_id = $transaction->id;
                        }
                    }

                    if (!empty($payment_id)) {
                        $ref_id_enc = base64_encode($transactionData['transaction_id']);
                        $response = array('status' => 1, 'msg' => 'Transaction completed!', 'ref_id' => $ref_id_enc);
                    }
                }
            } else {
                $response['msg'] = $api_error;
            }
        }

        return json_encode($response);
    }
}
