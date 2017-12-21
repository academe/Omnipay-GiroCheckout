<?php

namespace Academe\GiroCheckout\Message;

/**
 * GiroCheckout Gateway Purchase Request
 *
 * @link http://api.girocheckout.de/en:girocheckout:introduction:start
 */

use Omnipay\Common\Exception\InvalidRequestException;
use Academe\GiroCheckout\Gateway;

class PurchaseRequest extends AuthorizeRequest
{
    /**
     * @var array List of payment types that a request supports.
     */
    protected $supportedPaymentTypes = [
        Gateway::PAYMENT_TYPE_CREDIT_CARD,
        Gateway::PAYMENT_TYPE_DIRECTDEBIT,
        Gateway::PAYMENT_TYPE_PAYPAL,
        Gateway::PAYMENT_TYPE_MAESTRO,
        Gateway::PAYMENT_TYPE_EPS,
        Gateway::PAYMENT_TYPE_GIROPAY,
        Gateway::PAYMENT_TYPE_PAYDIREKT,
    ];

    /**
     * @var string The type of transaction being requested.
     */
    protected $transactionType = self::TRANSACTION_TYPE_SALE;
}
