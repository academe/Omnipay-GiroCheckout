<?php

namespace Academe\GiroCheckout\Message;

/**
 * GiroCheckout Gateway Repeat Authorization Request
 *
 * @link http://api.girocheckout.de/en:girocheckout:creditcard:start#recurring_credit_card_payment
 */

use Omnipay\Common\Exception\InvalidRequestException;

class RepeatPurchaseRequest extends PurchaseRequest
{
    /**
     * @var string The type of transaction being requested.
     */
    protected $transactionType = self::TRANSACTION_TYPE_SALE;

    /**
     * @var string The resource path, appended to the endpoint base URL.
     */
    protected $endpointPath = 'transaction/payment';

    /**
     * @var string
     */
    protected $recurring = self::RECURRING_YES;
}
