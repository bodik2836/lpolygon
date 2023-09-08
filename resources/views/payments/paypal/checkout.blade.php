@extends('layouts.default')

@section('content')

    <div class="container">
        <div id="container">
            <p>Item name: <?= $product['name'] ?></p>
            <p>Price: <?= '$' . $product['price'] ?></p>
            <div id="paypal-button-container"></div>
        </div>
    </div>


    <script data-sdk-integration-source="integrationbuilder_sc"></script>
    <script
        src=https://www.paypal.com/sdk/js?client-id=<?= env('PAYPAL_CLIENT_ID') ?>&components=buttons&enable-funding=venmo,paylater&currency=USD></script>
    <script>
        const FUNDING_SOURCES = [
            // EDIT FUNDING SOURCES
            paypal.FUNDING.PAYPAL
        ];
        FUNDING_SOURCES.forEach(fundingSource => {
            paypal.Buttons({
                fundingSource,

                style: {
                    layout: 'vertical',
                    shape: 'rect',
                    color: (fundingSource == paypal.FUNDING.PAYLATER) ? 'gold' : '',
                },

                createOrder: async (data, actions) => {
                    return actions.order.create(
                        {
                            "purchase_units": [{
                                "custom_id": "<?= $product['sku'] ?>",
                                "description": "<?= $product['name'] ?>",
                                "amount": {
                                    "currency_code": "<?= $product['currency'] ?>",
                                    "value": <?= $product['price'] ?>,
                                    "breakdown": {
                                        "item_total": {
                                            "currency_code": "<?= $product['currency'] ?>",
                                            "value": <?= $product['price'] ?>
                                        }
                                    }
                                },
                                "items": [
                                    {
                                        "name": "<?= $product['name'] ?>",
                                        "description": "<?= $product['name'] ?>",
                                        "unit_amount": {
                                            "currency_code": "<?= $product['currency'] ?>",
                                            "value": <?= $product['price'] ?>
                                        },
                                        "quantity": "1",
                                        "category": "DIGITAL_GOODS"
                                    },
                                ]
                            }]
                        }
                    );
                },

                onApprove: async (data, actions) => {
                    return actions.order.capture().then(
                        orderData => {
                            let postData = { paypal_order_check: 1, order_id: orderData.id, _token: "<?= csrf_token() ?>" };
                            fetch(window.location.origin + '/payments/paypal/checkout_validate', {
                                method: 'POST',
                                headers: {'Accept': 'application/json'},
                                body: encodeFormData(postData)
                            })
                                .then((response) => response.json())
                                .then((result) => {
                                    if ( result.status == 1 ) {
                                        console.log(result)
                                        window.location.href = window.location.origin + "/payments/paypal/status/" + result.ref_id;
                                    } else {
                                        console.log(result);
                                    }
                                })
                                .catch(error => console.log(error));
                        }
                    );
                },
            }).render("#paypal-button-container");
        })

        const encodeFormData = (data) => {
            let form_data = new FormData();

            for ( let key in data ) {
                form_data.append(key, data[key]);
            }
            return form_data;
        }
    </script>
@endsection
