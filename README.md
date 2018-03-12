# Omnipay: GiroCheckout

**GiroCheckout driver for the Omnipay PHP payment processing library**

[![Build Status](https://travis-ci.org/academe/Omnipay-GiroCheckout.svg?branch=master)](https://travis-ci.org/academe/Omnipay-GiroCheckout)
[![Latest Stable Version](https://poser.pugx.org/academe/omnipay-girocheckout/v/stable)](https://packagist.org/packages/academe/omnipay-girocheckout)
[![Latest Unstable Version](https://poser.pugx.org/academe/omnipay-girocheckout/v/unstable)](https://packagist.org/packages/academe/omnipay-girocheckout)
[![Total Downloads](https://poser.pugx.org/academe/omnipay-girocheckout/downloads)](https://packagist.org/packages/academe/omnipay-girocheckout)
[![License](https://poser.pugx.org/academe/omnipay-girocheckout/license)](https://packagist.org/packages/academe/omnipay-girocheckout)

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP 5.3+.
This package implements [Girocheckout](http://api.girocheckout.de/en:girocheckout:general:start)
support, and supports PHP 5.5+.

This branch supports Omnipay 2.x and will be maintained. For use of of Omnipay 3.x see [master branch](https://github.com/academe/Omnipay-GiroCheckout/tree/master).

Table of Contents
=================

   * [Omnipay: GiroCheckout](#omnipay-girocheckout)
   * [Table of Contents](#table-of-contents)
   * [Installation](#installation)
   * [Authentication](#authentication)
   * [Credit Card Payment Type](#credit-card-payment-type)
      * [Credit Card Authorize](#credit-card-authorize)
         * [Basic Authorize](#basic-authorize)
            * [Credit Card Complete Authorize](#credit-card-complete-authorize)
            * [Credit Card Authorize Notify](#credit-card-authorize-notify)
         * [Create a Reusable Card Reference](#create-a-reusable-card-reference)
         * [Offline Repeat Authorize](#offline-repeat-authorize)
      * [Credit Card Purchase Transactions](#credit-card-purchase-transactions)
      * [Credit Card Capture](#credit-card-capture)
      * [Credit Card Refund](#credit-card-refund)
      * [Credit Card Void](#credit-card-void)
   * [Direct Debit Payment Type](#direct-debit-payment-type)
      * [Basic Authorize](#basic-authorize-1)
         * [Create a Reusable Direct Debit Card Reference](#create-a-reusable-direct-debit-card-reference)
         * [Offline Direct Debit Payment](#offline-direct-debit-payment)
      * [Direct Debit Capture/Refund/Void](#direct-debit-capturerefundvoid)
   * [PayPal Payment Type](#paypal-payment-type)
      * [PayPal Purchase](#paypal-purchase)
   * [Giropay Payment Type](#giropay-payment-type)
      * [Giropay Issuers List](#giropay-issuers-list)
      * [Giropay Bank Capabilities](#giropay-bank-capabilities)
      * [Giropay Purchase](#giropay-purchase)
      * [Giropay Sender Details](#giropay-sender-details)
      * [Giropay ID (age verification)](#giropay-id-age-verification)
   * [Paydirekt Payment Type](#paydirekt-payment-type)
   * [Payment Page Payment Type](#payment-page-payment-type)
      * [Payment Page Projects List](#payment-page-projects-list)
      * [Cancel Url](#cancel-url)

# Installation

Using composer, this 2.x branch can be installed like this:

    composer require academe/omnipay-girocheckout:~2.0

# Authentication

A GiroCheckout merchant accoutn is first set up.
Within that account, one of more projects are set up.
Each project is configured to run just one payment type (e.g. credit card, direct debit).

Each project contains a pre-shared secret.
That secret is used to sign all messages, in both diections (requests to the gateway,
responses to those requests, and server request notification messages).

So for each payment type, you will have the following matched authentication details,
required for all interaction:

* Merchant ID
* Project ID
* Pre-shared secret (passphrase)
* Payment type

A gateway could be set up like this:

```php
use Omnipay\Omnipay;

// The backward slashes are needed to make the driver base class absolute.
// An issue will be raised against Omnipay Common to fix this.
$gateway = Omnipay::create('GiroCheckout');

// The IDs can be given as integers in strings.
$gateway->setMerchantID('3610000');
$gateway->setProjectID('37000');
$gateway->setProjectPassphrase('ZFXDMpXDMpVV9Z');
// Other payment types are supported.
$gateway->setPaymentType(Gateway::PAYMENT_TYPE_CREDIT_CARD);

// or

$gateway->initialize([
    'merchantId' => 3610000,
    'projectId' => 37000,
    'projectPassphrase' => 'ZFXDMpXDMpVV9Z',
    'paymentType' => Gateway::PAYMENT_TYPE_CREDIT_PAYPAL,
]);
```

# Credit Card Payment Type

This payment type supports authorize and purchase.
A card can be tokenised during a transaction and used again for future
payments with the user present, or for offline repeat payments.

The capture/refund/void methods are also available.

## Credit Card Authorize

### Basic Authorize

A simple authorize will look like this:

```php
$gateway->setPaymentType(Gateway::PAYMENT_TYPE_CREDIT_CARD);

$authRequest = $gateway->authorize([
    'transactionId' => $yourMerchantTransactionId,
    'amount' => '1.23',
    'currency' => 'EUR',
    'description' => 'Mandatory reason for the transaction',
    'language' => 'en',
    'returnUrl' => 'url to bring the user back to the merchant site',
    'notifyUrl' => 'url for the gateway to send direct notifications',
    'mobile' => false,
]);
```

The response will be a redirect to the gateway, where the user will enter their
credit card details.
The language setting will define the language used in the user interface and in
error messages returned in the response.

On completion, the result of the transaction will sent to the merchant site in
two ways (both will be used):

* Via the notification URL.
* Through query parameters given to the return URL.

The `notifyUrl` and the `returnUrl` can both take custom query parameters.
The gateway will merge in its own parameters when using the URLs.

#### Credit Card Complete Authorize

When the user returns to the `returnUrl`, the transaction result can be extracted
like this:

```php
$completeRequest = $gateway->completeAuthorize();
$completeResponse = $completeRequest->send();

// Available standard Omnipay details:

$completeResponse->getCode();
$completeResponse->getMessage();
$completeResponse->isSuccessful();
$completeResponse->isCancelled();
$completeResponse->getTransactionStatus();
$completeResponse->getTransactionReference();
```

If the response fails its hash check against the shared secret, then an exception
will be raised on `sebd()`. The raw response data can still be read for logging as
`$completeRequest->getData()`, which returns an array.

The notification handler, on the `notifyUrl`, is set up like this:

```php
$notifyRequest = $gateway->acceptNotification();
$notifyResponse = $notifyRequest->send();
```

If `createCard` is set in the `$gateway` or `acceptNotification()`,
then the reusable card token will be available in the notify response,
along with the usual Omnipay methods:

```php
$notifyResponse->getCardReference()
```

Once the authorisation is complete, the amount still needs to be captured.

#### Credit Card Authorize Notify

Exactly the same rules apply as to the `completeAuthorize` request - an exception
will be raised if the hash does not validate; the same standard Omnpay result
details are available.

### Create a Reusable Card Reference

When authorizing, the gateway can be asked to create a reusable card reference.
This flag in the authorize request will trigger that:

```php
$authRequest = $gateway->authorize([
    ...
    'createCard' => true,
]);
```

After the authorize completes, the card reference is fetched using this request:

```php
$getCardRequest = $gateway->getCard([
    'transactionReference' => 'otiginal transaction reference',
]);
$getCardResponse = $getCardRequest->send();

// The reusable `cardReference` is available here:
$cardReference = $getCardResponse->getTransactionReference();

// Other details about the card that may be useful:
$getCardResponse->getNumberMasked();
$getCardResponse->getExpiryYear();
$getCardResponse->getExpiryMonth();
```

**Note:** The fetching of the card reference can be automated when completing
an authorisation, by setting the `createCard` parameter when completing:

```php
$completeRequest = $gateway->completeAuthorize([
    'createCard' => true,
]);
$completeResponse = $completeRequest->send();

echo 'Card Ref: ' . $completeResponse->getCardReference();

// Card Ref: 6317fda4cce2192fecc51ba244a91a08
```

The `$cardReference` is then used for authorizing:

```php
$authRequest = $gateway->authorize([
    ...
    'cardReference' => $cardReference,
]);
```

The user will be redirected to the payment gateway like the basic authorize,
but the credit card details will be already completed (and cannot be changed
by the user).
The user will need to enter their `CVV` to authorise use of the card.

By setting the `recurring` parameter flag, even the need for the CVV to be
entered can be avoided. This feature is only available on application.
Even through the user will be redirected to the gateway, they will be redirected
back again immediately with no need to enter any card or CVV details.
However, the redirect does give the gateway the option to insert some additional
validation if it needs to.

### Offline Repeat Authorize

When a reusable `cardReference` is used, the need to redirect the user to the
gateway can be avoided by resetting the `paymentPage` parameter.

```php
$authRequest = $gateway->authorize([
    ...
    'paymentPage' => false,
]);
```

This can be used without the user being present, so is useful for subscriptions
and other repeated payments.

## Credit Card Purchase Transactions

Replace `purchase` in place of `authorize`.

## Credit Card Capture

The required amount can be captured using this request:

```php
$captureRequest = $gateway->capture([
    'transactionId' => $yourMerchantTransactionId,
    'amount' => '1.23',
    'currency' => 'EUR',
    'description' => 'Capture reason is required',
    'transactionReference' => 'original authorize transaction reference',
]);

$captureRersponse = $captureRequest->send();

// Check if successful:
$captureRersponse->isSuccessful();

// Some other details:
$captureRersponse->getCode();
$captureRersponse->getMessage();
$captureRersponse->getTransactionReference();
```

## Credit Card Refund

A refund of the full or a partial amount can be done using the `refund` message.
It is used in exactly the same way as the `capture` message.

## Credit Card Void

A transaction can be completely voided like this:

```php
$voidRequest = $gateway->capture([
    'transactionReference' => 'original authorize transaction reference',
]);

$voidResponse = $voidRequest->send();

// Check if successful:
$captureRersponse->isSuccessful();
```

# Direct Debit Payment Type

The Direct Debit payment type works in a very similar way to the Credit Card
payment type.
The main differences are:

* The bank account is identified by an IBAN or a sort code/bank code/BMZ and account number (SCAN).
* When fetching the saved "cardReference", details you get back include `ibanMasked`.
* Running an authorize or payment without a paymentform, you can supply IBAN, SCAN or cardReference.

## Basic Authorize

A simple authorize will look like this:

```php
$gateway->setPaymentType(Gateway::PAYMENT_TYPE_DIRECTDEBIT);

$authRequest = $gateway->authorize([
    'transactionId' => $yourMerchantTransactionId,
    'amount' => '4.56',
    'currency' => 'EUR',
    'description' => 'Mandatory reason for the transaction',
    'language' => 'en',
    'returnUrl' => 'url to bring the user back to the merchant site',
    'notifyUrl' => 'url for the gateway to send direct notifications',
    'mobile' => false,
    // Parameters specific to Direct Debit, all optional:
    'mandateReference' => '...',
    'mandateSignedOn' => '...',
    'mandateReceiverName' => '...',
    'mandateSequence' => '...',
]);
```

The `mandateSignedOn` is a date in the forma `YYYY-MM-DD`.
A `DateTime` or derived object can ve supplied instead, which includes `Carbon\Carbon` objects.

### Create a Reusable Direct Debit Card Reference

The Direct Debit payment type supports saving the details of the collected bank details
as a `cardReference`. Triggering the saving is done by turning on `creatCard` like this:

```php
$authRequest = $gateway->authorize([
    ...
    'createCard' => true,
]);
```

The `cardReference` can be fetched in the same way as the Credit Card payment type.

### Offline Direct Debit Payment

The payment page redirect can be turned off to support offline payment, without the
user being present:

```php
$authRequest = $gateway->authorize([
    ...
    'paymentPage' => false,
]);
```

When making an offline payment, one of the following must be supplied:

* cardReference
* IBAN
* SCAN

```php
$authRequest = $gateway->authorize([
    ...
    'cardReference' => '13b5ca34a8389774690154bcc0da0a8e',
    // or
    'iban' => 'DE87123456781234567890',
    // or
    'accountHolder' => 'Name',
    'bankCode' => '12345678',
    'bankAccount' => '1234567890',
]);
```

## Direct Debit Capture/Refund/Void

These operate in exactly the same way as for Credit Card payments.

# PayPal Payment Type

The PayPal payment type supports only one request type, `purchase`.

## PayPal Purchase

A simple purchase will look like this:

```php
$gateway->setPaymentType(Gateway::PAYMENT_TYPE_PAYPAL);

$authRequest = $gateway->purchase([
    'transactionId' => $yourMerchantTransactionId,
    'amount' => '7.89',
    'currency' => 'EUR',
    'description' => 'Mandatory reason for the transaction',
    'returnUrl' => 'url to bring the user back to the merchant site',
    'notifyUrl' => 'url for the gateway to send direct notifications',
]);
```

The response should be a redirect to the remote gateway.

On return from the gateway, the result can be accessed using the
`$response -> $gateway->complete()->send()` message like for previous payment types.

The back-channel notification handler will be sent the usual details too.

# Giropay Payment Type

This payment type only works for payments in Euros, and only for issuing
banks that have registered with the service (over 1400 to date).

As well as making payments, there are a number of supporting API methods.

## Giropay Issuers List

This method returns a list of issuing banks that are regisered for this service.
The list would normally be used to present to the end user so they can choose
their personal bank.

```php
$gateway->setPaymentType(Gateway::PAYMENT_TYPE_GIROPAY);

$request = $gateway->getIssuers();

$response = $resquest->send();

// The list of named banks is indexed by their BIC.

if ($response->isSuccessful()) {
    $bankList = $response->getIssuerArray();

    var_dump($bankList);
}

// array(1407) {
//  ["BELADEBEXXX"]=>
//  string(38) "Landesbank Berlin - Berliner Sparkasse"
//  ["BEVODEBBXXX"]=>
//  string(18) "Berliner Volksbank"
//  ["GENODEF1P01"]=>
//  string(27) "PSD Bank Berlin-Brandenburg"
//  ["WELADED1WBB"]=>
//  string(9) "Weberbank"
//  ...
// }

```

## Giropay Bank Capabilities

Once an issuing bank is chosen, its capabilities can be checked.
This tests whether it supports Giropay, or Giropay+ID, or both.

This list can also be retrieved on the front end only using the
[Bank Selection Widget](http://api.girocheckout.de/en:girocheckout:giropay:start#bank_selection_widget).
There is no direct support for the widget in this driver.

```php
$request = $gateway->getBankStatus([
    'bic' => 'TESTDETT421',
]);

$response = $resquest->send();

// Both return boolean.

$supportsGiropay = $response->hasGiropay();
$supportsGiropayId = $response->hasGiropayId();
```

## Giropay Purchase

There is no `authorize` capability, just `purchase`.

```php
$request = $gateway->purchase([
    'transactionId' => $transactionId,
    'amount' => '1.23',
    'currency' => 'EUR',
    'description' => 'Transaction ' . $transactionId,
    'returnUrl' => 'url to bring the user back to the merchant site',
    'notifyUrl' => 'url for the gateway to send direct notifications',
    'bic' => 'TESTDETT421', // mandatory
    'info1Label' => 'My Label 1',
    'info1Text' => 'My Text 1',
]);
```

The result will be a redirect to the gateway or bank.

On return, the usual `completePurchase` and `acceptNotification` messages will provide
the result of the transaction attempt.

The final result includes the following methods to inspect additional details:

```php
$notifyResponse->getResultAvs();
$notifyResponse->getObvName();
$notifyResponse->isAgeVerificartionSuccessful();
```

## Giropay Sender Details

Details of the sender can be fetched given a successful transaction.

```php
$request = $gateway->getSender([
    'transactionReference' => '6b65a235-e7c1-464f-b238-ea4ea0bc647f',
]);

$response = $request->send();
```

Details include:

```php
$response->getAccountHolder();
// string(17) "Stefan Adlerhorst"

$response->getIban();
// string(22) "DE46940594210000012345"

$response->getBic();
// string(11) "TESTDETT421"
```

## Giropay ID (age verification)

Use the `Giropay-ID` payment type to just perform age verification without
making a payment. You can leave out the amount, currency and description, as
none of those are sent to the gateway.

```php
$gateway->setPaymentType(Gateway::PAYMENT_TYPE_GIROPAY_ID);
```

# Paydirekt Payment Type

This is the only payment type that accepts a shopping cart details and
a `CreditCard` object for shipping details.

Capabilities of this payment type include `authorize` and `purchase`.
An authorization transaction can be further processed though `capture`
and `void`. A purchase transaction can accept a `refund`.

The gateway requires cart item prices to be in minor units.
Since Omnipay 2.x does not define the units cart items use, some assumptions
will be made and conversions performed as follows (all these formats are
treated as the same amount):

* String '1.23' => 123
* String '123' => 123
* Integer 123 => 123
* Float 1.23 => 123

Further documentation and examples will follow.

# Payment Page Payment Type

This payment type offers the customer all payment methods available from the 
merchant rather than displaying them seperately. The payment page allows the
customer to select the payment method they wish to use and then the selected
payment is initialized accordingly.

## Payment Page Projects List

This method returns a list of possible GiroCockpit projects. The list contains the following elements:

* Project Id
* Project Name
* Paymethod Number (see [Payment methods](http://api.girocheckout.de/en:girocheckout:paypage:start#payment_methods))
* Mode (_TEST_ or _LIVE_)

```php
$gateway->setPaymentType(Gateway::PAYMENT_TYPE_PAYMENTPAGE);

$request = $gateway->getProjects();

$response = $request->send();

if ($response->isSuccessful()) {
    $projects = $response->getProjects();

    var_dump($projects);
}

// array(5) {
//     [0]=>
//     array(4) {
//       ["id"]=>
//       string(5) "37570"
//      ["name"]=>
//       string(11) "Giropay One"
//       ["paymethod"]=>
//      string(1) "1"
//       ["mode"]=>
//       string(4) "TEST"
//     }
//    ...
// }

```

## Cancel Url

The Payment Page `cancelUrl` differs to the rest of the payment types as it does not return the transaction cancelled details. Therefore, you must **not** call `completeAuthorise` when returning to the merchant site.
