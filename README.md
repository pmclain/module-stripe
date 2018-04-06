# Magento 2 Stripe Integration
[![Build Status](https://travis-ci.org/pmclain/module-stripe.svg?branch=master)](https://travis-ci.org/pmclain/module-stripe)  

Accept credit card payments through the Stripe payment gateway.

* Supports Magento Instant Purchase for One Click Checkout
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
* 3D Secure support for one-time and vault payments

## Installation
#### Composer
In your Magento 2 root directory run  
`composer require pmclain/module-stripe`  
`bin/magento setup:upgrade`  

## Magento Version Requirements
| Release | Magento Version |
| ------- | --------------- |
| 1.x.x   | 2.2.x           | 
| 1.x.x   | 2.1.x           |
| None    | 2.0.x           |

## Configuration
The configuration can be found in the Magento 2 admin panel under  
Store->Configuration->Sales->Payment Methods->Stripe

## Feature Roadmap
There is no ETA for implementation, but here is the list in order of priority.
1. Multi-shipping address support
2. Stripe Radar

## License
Open Software License v3.0
