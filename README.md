# Magento 2 Stripe Integration
Accept credit card payments through the Stripe payment gateway.

* Securely accept customer payments using the Stripe.js tokenization when
collecting all payments.
* Provide customers option of storing payment information for future 
transactions.
* Stored customer card information can be used for orders created in the
frontend or backend.
* Stored cards deleted by customer in Magento are also removed from the
corresponding Stripe customer profile.
* New payments can be authorize or authorize and capture.
* Authorized payments can be captured online during invoice creation.
* Full and partial refund support when creating credit memos.

## Installation
#### Composer
In your Magento 2 root directory run  
`composer require pmclain/module-stripe`  
`bin/magento setup:upgrade`  

#### Manual
The module can be installed without Composer by downloading the desired
release from https://github.com/pmclain/module-stripe/releases and placing
the contents in `app/code/Pmclain/Stripe/`  
The module depends on the Stripe PHP-SDK which should be added to your
project via composer by running `composer require stripe/stripe-php:5.2.0`
With the module files in place and the Stripe SDK installed,
run `bin/magento setup:upgrade`

## Magento Version Requirements
| Release | Magento Version |
| ------- | --------------- |
| 1.x.x   | 2.2.x           | 
| 1.x.x   | 2.1.x           |
| 0.0.3   | 2.0.x           |

## Configuration
The configuration can be found in the Magento 2 admin panel under  
Store->Configuration->Sales->Payment Methods->Stripe

## License
GNU GENERAL PUBLIC LICENSE Version 3
