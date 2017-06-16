# Magento 2 Stripe Integration
Accept credit card payments through the Stripe payment gateway.

* Securely accept customer payments using the Stripe.js tokenization when
collecting all payments.
* Provide customers option of storing payment information for future 
transactions.
* Stored customer card information can be used for orders created in the
frontend or backend.
* New payments can be authorize or authorize and capture.
* Authorized payments can be captured online during invoice creation.
* Full and partial refund support when creating credit memos.

## Installation
In your Magento 2 root directory run  
`composer require pmclain/module-stripe`  
`bin/magento setup:upgrade`

## Configuration
The configuration can be found in the Magento 2 admin panel under  
Store->Configuration->Sales->Payment Methods->Stripe

## License
GNU GENERAL PUBLIC LICENSE Version 3