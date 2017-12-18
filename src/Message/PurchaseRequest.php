<?php

namespace Academe\GiroCheckout\Message;

/**
 * GiroCheckout Gateway Purchase Request
 *
 * @link http://api.girocheckout.de/en:girocheckout:introduction:start
 */

use Omnipay\Common\Exception\InvalidRequestException;

class PurchaseRequest extends AuthorizeRequest
{
    /**
     * @var string The type of transaction being requested.
     */
    protected $transactionType = self::TRANSACTION_TYPE_SALE;
}
