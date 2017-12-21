# Omnipay: GiroCheckout

**GiroCheckout driver for the Omnipay PHP payment processing library**

[![Build Status](https://travis-ci.org/academe/Omnipay-GiroCheckout.svg?branch=master)](https://travis-ci.org/academe/Omnipay-GiroCheckout)
[![Latest Stable Version](https://poser.pugx.org/academe/omnipay-girocheckout/v/stable)](https://packagist.org/packages/academe/omnipay-girocheckout)
[![Latest Unstable Version](https://poser.pugx.org/academe/omnipay-girocheckout/v/unstable)](https://packagist.org/packages/academe/omnipay-girocheckout)
[![Total Downloads](https://poser.pugx.org/academe/omnipay-girocheckout/downloads)](https://packagist.org/packages/academe/omnipay-girocheckout)
[![License](https://poser.pugx.org/academe/omnipay-girocheckout/license)](https://packagist.org/packages/academe/omnipay-girocheckout)

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP 5.3+. This package implements Sage Pay support for Omnipay.
This version supports PHP 5.6+.

Table of Contents
=================

   * [Omnipay: GiroCheckout](#omnipay-girocheckout)
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
use Academe\GiroCheckout\Gateway;
use Omnipay\Omnipay;

// The backward slashes are needed to make the driver base class absolute.
// An issue will be raised against Omnipay Common to fix this.
$gateway = Omnipay::create('\\' . Gateway::class);

// The IDs can be given as integers ir strings.
$gateway->setMerchantID('3610000');
$gateway->setProjectID('37000');
$gateway->setProjectPassphrase('ZFXDMpXDMpVV9Z');
// Other payment types are supported.
$gateway->setPaymentType(Gateway::PAYMENT_TYPE_CREDIT_CARD);
```

# Credit Card Payment Type

This payment type supports authorize and purchase.
A card can be tokenised during a transaction and used again for future
payments with the user present, or for offline repeat payments.

The capture/refund/void methods are also available.

## Credit Card Authorize

### Basic Authorize

A simple authoirze will look likle this:

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
    'mobileOptimise' => false,
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

### Credit Card Purchase Transactions

Replace `purchase` in place of `authorize`.

### Credit Card Capture

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

### Credit Card Refund

A refund of the full or a partial amount can be done using the `refund` message.
It is used in exactly the same way as the `capture` message.

### Credit Card Void

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

### Basic Authorize

A simple authoirze will look likle this:

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
    'mobileOptimise' => false,
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

### Direct Debit Capture/Refund/Void

These operate in exactly the same way as for Credit Card payments.

