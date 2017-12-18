<?php

namespace Academe\GiroCheckout\Message;

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * GiroCheckout Gateway Authorization Request
 *
 * @link http://api.girocheckout.de/en:girocheckout:creditcard:start#recurring_credit_card_payment
 */
class RepeatPurchaseRequest extends PurchaseRequest
{
    /**
     * @var string The type of transaction being requested.
     */
    protected $transactionType = self::TRANSACTION_TYPE_SALE;

    /**
     * @var string
     */
    protected $requestEndpoint = 'https://payment.girosolution.de/girocheckout/api/v2/transaction/payment';

    /**
     * @var string
     */
    protected $recurring = self::RECURRING_YES;
}
